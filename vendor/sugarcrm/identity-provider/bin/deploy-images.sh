#!/bin/bash
cd /tmp
rm -rf /tmp/IdentityProvider
git clone git@github.com:sugarcrm/IdentityProvider.git
cd IdentityProvider

read -p "Your Github token (optional): " token

if [ -z "$token" ]
then
    docker build --pull -t registry.sugarcrm.net/identity-provider/identity-provider:latest -f app/deploy/Dockerfile .
else
    docker build --pull --build-arg COMPOSER_AUTH='{"github-oauth": {"github.com": "'$token'"}}' -t registry.sugarcrm.net/identity-provider/identity-provider:latest -f app/deploy/Dockerfile .
fi

read -p "Push to sugar registry? [y/n]" yn
case $yn in
  [Yy]* ) docker push registry.sugarcrm.net/identity-provider/identity-provider:latest
;;
esac

read -p "Push to quay? [y/n]" yn
case $yn in
  [Yy]* )
     tag='manual-'`date -u +%Y%m%d%H%M`
     tagHash='manual-git-'`git log -1 --format=format:%h`
     docker tag registry.sugarcrm.net/identity-provider/identity-provider:latest quay.io/sugarcrm/idm-login:$tag
     docker tag registry.sugarcrm.net/identity-provider/identity-provider:latest quay.io/sugarcrm/idm-login:$tagHash
     docker tag registry.sugarcrm.net/identity-provider/identity-provider:latest quay.io/sugarcrm/idm-login:latest-manual
     docker login quay.io
     docker push quay.io/sugarcrm/idm-login:$tag
     docker push quay.io/sugarcrm/idm-login:$tagHash
     docker push quay.io/sugarcrm/idm-login:latest-manual
  ;;
esac
