#!/bin/bash
#
# Gets session cookies for logged in users for use with BackstopJS.  
#
# To use in a VRT scenario use something like
#  "cookiePath": "../../web/backstop_data/engine_scripts/cookies-uid1.json"

if [ -z "$1" ]; then
  echo "You must designate which environment to get a cookie for" >&2
  echo "  e.g. get-logged-in-cookies dev" >&2
  exit 2
fi

# Configuration.
pantheon_site="nyu-stern-d9"
cookie_output_file="../../web/backstop_data/engine_scripts/cookies-uid1.json"
local_ddev_domain="nyu-stern.ddev.site"
live_domain="www.stern.nyu.edu"
# To use these you'll also need to adjust the code below to follow some pattern for the custom domains.
environments_with_custom_domains=('test' 'develop' 'fast' 'freeze' 'feedback')
# End of configuration.

environment="$1"

# Get the URL to log in.
# @todo Consider getting separate cookies for multiple user roles.
if [ "$environment" == "local" ]; then
  uli_url=$(ddev drush user:login -l "$local_ddev_domain")
else
  uli_url=$(terminus drush "$pantheon_site"."$environment" -- user:login)
fi

# Get HTTP response headers when hitting that URL.
http_headers=$(curl --silent --location --output /dev/null --dump-header - "$uli_url")

# Only get the session cookie
session_cookie=$(echo "$http_headers" | grep 'set-cookie:' | grep --only-matching 'S\?SESS[^;]*' | head -n1)
echo "Found cookie" >&2
echo "  $session_cookie"  >&2

cookie_name=$(echo "$session_cookie" | cut -d "=" -f 1)
cookie_value=$(echo "$session_cookie" | cut -d "=" -f 2)

if [ "$1" == "local" ]; then
  # @todo consider getting a more robust URL via `ddev describe -j`
  domain="$local_ddev_domain"
elif [ "$1" == "live" ]; then
  domain="www.stern.nyu.edu"
elif [[ " ${environments_with_custom_domains[*]} " =~ [[:space:]]${1}[[:space:]] ]]; then
  domain="www-$environment.stern.nyu.edu"
else
  domain="$environment-$pantheon_site.pantheonsite.io"
fi

# Hit a page with the session cookie to make the
#   "You've just used your one-time login link..."
# message to disappear.
curl --silent --location  --output /dev/null --header "Cookie: $cookie_name=$cookie_value" "https://$domain/user"

cookie_domain=".$domain"

cookie_json='[{"domain":"'
cookie_json+="$cookie_domain"
cookie_json+='","path":"/","name":"'
cookie_json+="$cookie_name"
cookie_json+='","value":"'
cookie_json+="$cookie_value"
cookie_json+='","expirationDate":1798790400,"hostOnly":false,"httpOnly":true,"secure":true,"session":false,"sameSite":"Lax"}]'

parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
cd "$parent_path"
touch $cookie_output_file
echo "$cookie_json" > $cookie_output_file
