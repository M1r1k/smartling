#!/bin/bash
#
# This helper script do installation of drupal cms for smartling demo site
#
################################

usage () {
    echo "Usage: $0 -user=<user> -pass=<password> -host=<host> -db=<db_name> [-h]
    -user, -host, -db is mandatory parameters. If -pass not set, password will bwe asked at prompt"
    exit 1
}

logit () {
    DATE=$(date +"[%Y-%m-%d %H:%M:%S]")
    echo -n "$DATE "
    case $1 in
        info) echo -n '[INFO] '
            ;;
        warn) echo -n '[WARNING] '
            ;;
        err)  echo -n '[ERROR] '
            ;;
        *) echo $1
            ;;
    esac
    echo $2
}

################################
# some usable variables
################################

# git url
GIT=$(which git)
if [ "x$GIT" = "x" ]; then
  logit err "Git not found in PATH. Exiting"
  exit 1
fi

# drush executable
DRUSH_EXEC=$(which drush)
[ "x$DRUSH_EXEC" = "x" ] && { logit err "Drush not found in PATH. Exiting"; exit 1; }

################################ 
# main code
################################

drush_e () {
    logit info "Start drush $1"
    $DRUSH_EXEC $*
    RESULT=$?
    [ $RESULT -ne 0 ] && { logit err "Drush $1 error $RESULT. Exiting"; exit $RESULT; }
    logit info "Done"
}

# read options
set -- $(getopt -n$0 -u -a --longoptions="user: pass: host: db:" "h" "$@") || usage

while [ $# -gt 0 ];do
    case "$1" in
        --user) UN=$2;shift;;
        --pass) PSWD=$2;shift;;
        --host) HOST=$2;shift;;
        --db)   DB=$2;shift;;
        -h)     usage;;
        --)     shift;break;;
        -*)     usage;;
        *)      break;;
    esac
    shift
done

# check params
[ "x$UN" == "x" ] && { logit err "-user option should be set"; usage; }

# ask password if it not set from command line
if [ "x$PSWD" == "x" ]; then
    read -s -p "Password: " PSWD
fi

[ "x$HOST" == "x" ] && { logit err "-host option should be set"; usage; }
[ "x$DB" == "x" ] && { logit err "-db option should be set"; usage; }

# search for git executable
logit info "Download source from repo"
$GIT clone https://github.com/Smartling/drupal-localization-module.git
RESULT=$?
[ $RESULT -ne 0 ] && { logit err "Git error $RESULT. Exiting"; exit $RESULT; }
logit info "Done"

mv drupal-localization-module/smartling-demo-install.make ./smartling-demo-install.make

drush_e make smartling-demo-install.make -y
drush_e site-install standard --db-url=mysql://$UN:$PSWD@$HOST/$DB --account-name=admin --account-pass=admin --site-name=Smartling -y

mkdir sites/all/modules/custom/
mv drupal-localization-module/smartling sites/all/modules/custom/

logit info "Download source from repo"
$GIT clone https://github.com/Smartling/api-sdk-php.git sites/all/modules/custom/smartling/api
RESULT=$?
[ $RESULT -ne 0 ] && { logit err "Git error $RESULT. Exiting"; exit $RESULT; }
logit info "Done"

chmod -R 777 sites/default/files
drush_e en admin_menu ultimate_cron -y
drush_e dis overlay toolbar -y
drush_e vset --exact ultimate_cron_poorman 0
drush_e en smartling -y
drush_e en smartling_demo_content -y
drush_e fra -y
drush_e cc all -y
drush_e cron-run defaultcontent_cron
drush_e cron

# don't like this but devs wanna this :)
logit info "Set owner to nginx"
if [ $EUID -eq 0 ]; then
    chown nginx.nginx -R ./drupal-7.23/
    RESULT=$?
    [ $RESULT -ne 0 ] && { logit err "chown error $RESULT. Exiting"; exit $RESULT; }
else
    logit warn "Can't do that, you should be root"
fi

logit info "Installation done"
exit 0 