#!/bin/bash
#
# This helper script do download smartling module and zip it
#
################################

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

# unzip
UNZIP=$(which unzip)
if [ "x$UNZIP" = "x" ]; then
  logit err "UnZip not found in PATH. Exiting"
  exit 1
fi

# drush executable
DRUSH_EXEC=$(which drush)
[ "x$DRUSH_EXEC" = "x" ] && { logit err "Drush not found in PATH. Exiting"; exit 1; }

drush_e () {
    logit info "Start drush $1"
    $DRUSH_EXEC $*
    RESULT=$?
    [ $RESULT -ne 0 ] && { logit err "Drush $1 error $RESULT. Exiting"; exit $RESULT; }
    logit info "Done"
}

# read options
set -- $(getopt -n$0 -u -a --longoptions="module: " "h" "$@") || usage

while [ $# -gt 0 ];do
    case "$1" in
        --module) MODULE=$2;shift;;
        -h)     usage;;
        --)     shift;break;;
        -*)     usage;;
        *)      break;;
    esac
    shift
done

# check params
[ "x$MODULE" == "x" ] && { logit err "-module option should be set"; usage; }

logit info "Remove folder with smartling modules"
rm -rf sites/all/modules/custom/smartling

logit info "Installing Connector module"
$UNZIP $MODULE -d ./sites/all/modules/custom
logit info "Enabling Connector module"
drush_e en smartling smartling_reports -y
drush_e en smartling_demo_content -y
logit info "Generate demo content"
drush_e cron -y
logit info "Prepare env for running tests"
drush_e en simpletest -y

logit info "Fill default smartling settings"
php -r "print json_encode(array('zh-hans' => 'zh-hans', 'ru' => 'ru'));" | drush_e vset --format=json smartling_target_locales -
php -r "print json_encode(array('zh-hans' => 'zh-CN', 'ru' => 'ru-RU', 'en' => 'en-US'));" | drush_e vset --format=json smartling_locales_convert_array -

logit info "Done"
exit 0