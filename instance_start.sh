#!/bin/bash

if [[ -z "${SUGAR_DB_TYPE}" || -z "${SUGAR_LICENSE_KEY}" || -z "${SUGAR_SITE_URL}" ]]; then
  echo "Missing One of the following variables SUGAR_DB_TYPE, SUGAR_LICENSE_KEY, SUGAR_SITE_URL, Removing config_si.php"
	rm -rf /var/www/html/config_si.php
fi

# append <?php to empty (size 0) config overrides
if [[ ! -s /var/www/html/config_override.php ]]; then
  echo "<?php " > /var/www/html/config_override.php
fi

if [[ -n "${MEMCACHED_PORT_11211_TCP_ADDR}" ]]; then
  echo "We have memcached running lets enable it"
  echo "

\$sugar_config['external_cache']['memcache']['host'] = '${MEMCACHED_PORT_11211_TCP_ADDR}';

" >> /var/www/html/config_override.php
fi

var="${SUGAR_DB_TYPE^^}_NAME"
if [[ -z "${!var}" ]]; then
  echo "Missing Variables ${SUGAR_DB_TYPE^^}_NAME, Removing config_si.php"
  rm -rf /var/www/html/config_si.php
fi

function version_gt() { test "$(echo "$@" | tr " " "\n" | sort -V | head -n 1)" != "$1"; }
function isCrontabSetup(){
    crontabReg=".*\/var\/www\/html.*cron\.php"
    COUNT=$(crontab -u www-data -l | grep "$crontabReg" | wc -l)
    if [[ ${COUNT} > 0 ]]; then
        return 0
    else
        return 1
    fi
}
function isQueueManagerSetup(){
    crontabReg=".*\/var\/www\/html.*queueManager\.php"
    COUNT=$(crontab -u www-data -l | grep "$crontabReg" | wc -l)
    if [[ ${COUNT} > 0 ]]; then
        return 0
    else
        return 1
    fi
}

if [[ ! -z "${SUGAR_VERSION}" ]]; then
  version_gt '7.7.999.0' "${SUGAR_VERSION}"
  if [[ $? -eq 1 ]]; then
    echo "Setting up Queue Manager"
    echo "
\$sugar_config['job_queue']['od'] = true;
\$sugar_config['job_queue']['runner'] = 'OD';
\$sugar_config['job_queue']['adapter'] = 'Sugar';
\$sugar_config['job_queue']['workload'] = 'Workload';
\$sugar_config['job_queue']['max_runtime'] = 10; // sec.
\$sugar_config['job_queue']['lock'] = 'CacheFile'; // only allow one at a time
\$sugar_config['cron']['max_cron_runtime'] = 3600;
" >> /var/www/html/config_override.php
    # check if cron is wanted
    if [[ -z ${SUGAR_DISABLE_CRON+x} ]]; then
      if isQueueManagerSetup; then
        echo "Queue Manager already configured"
      else
        (crontab -u www-data -l 2>/dev/null; echo "* * * * * cd /var/www/html && /usr/local/bin/php -f queueManager.php 2>&1") | crontab -u www-data -
      fi
    fi
  fi
fi

function isMaxPostSizeSet(){
    COUNT=$(grep "post_max_size" /usr/local/etc/php/conf.d/setting.ini | wc -l)
    if [[ ${COUNT} > 0 ]]; then
        return 0
    else
        return 1
    fi
}

if isMaxPostSizeSet; then
    echo "Max Post size set"
else
    echo "post_max_size = 200M" >> /usr/local/etc/php/conf.d/setting.ini
fi

## start cron
service cron start
# check if cron is wanted
if [[ -z ${SUGAR_DISABLE_CRON+x} ]]; then
  ## inserting cron command for the www-data user
  if isCrontabSetup; then
    echo "Crontab already configured";
  else
    (crontab -u www-data -l 2>/dev/null; echo "* * * * * cd /var/www/html && /usr/local/bin/php -f cron.php 2>&1") | crontab -u www-data -
  fi
fi
apache2-foreground