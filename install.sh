#!/bin/bash

# $1-branch ; $2-branch flag '-b' (syntax for all addons in addonsdl.sh)
# for '$branch' before 'addonstitle.sh' exist ( ./install UPDATE -b : branch=UPADTE )
if [[ $# == 2 && $2 == '-b' ]]; then
	branch=$1
else
	branch=master
fi

# for 'installstart' before 'addonslist.php' exist
if [[ ! -e /srv/http/addonslist.php ]]; then
	gitpath=https://github.com/rern/RuneAudio_Addons/raw/$branch/srv/http
	wget -qN --no-check-certificate $gitpath/addonslist.php -P /srv/http
	wget -qN --no-check-certificate $gitpath/addonstitle.sh -P /srv/http
fi

# change version number in RuneAudio_Addons/srv/http/addonslist.php

alias=addo

. /srv/http/addonstitle.sh

installstart $@

#0temp0
# delete runeui.min.js.backup from .../js/ after next RuneUIe update
sed -i '/jquery.mobile.custom.min.js/ d' /srv/http/app/templates/footer.php
redis-cli del notifysec zoomlevel browser &> /dev/null
#1temp1

getinstallzip

. /srv/http/addonsedit.sh

echo -e "$bar Modify files ..."
#----------------------------------------------------------------------------------
file=/srv/http/app/templates/header.php
echo $file

string=$( cat <<'EOF'
    <link rel="stylesheet" href="<?=$this->asset('/css/addonsinfo.css')?>">
EOF
)
appendH 'runeui.css'

string=$( cat <<'EOF'
            <li><a id="addons"><i class="fa"></i> Addons</a></li>
EOF
)
appendH -n +1 'logout.php'
#----------------------------------------------------------------------------------
file=/srv/http/app/templates/footer.php
echo $file

string=$( cat <<'EOF'
<script src="<?=$this->asset('/js/addonsinfo.js')?>"></script>
<script src="<?=$this->asset('/js/addonsmenu.js')?>"></script>
EOF
)
appendH 'code.jquery.com'

# separate to keep out of uninstall
if ! grep 'jquery.mobile.custom.min.js' $file; then
	string=$( cat <<'EOF'
<script src="<?=$this->asset('/js/vendor/jquery.mobile.custom.min.js')?>"></script>
EOF
)
	sed -i "/jquery-2.1.0.min.js/ a$string" $file
fi
#----------------------------------------------------------------------------------
file=/etc/nginx/nginx.conf
if ! grep -q 'ico|svg' $file; then
	echo $file
	commentS 'gif\|ico'
	string=$( cat <<'EOF'
        location ~* (.+)\.(?:\d+)\.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
EOF
)
	appendS 'gif\|ico'
	
	svg=0
else
	svg=1
fi

# set sudo no password
echo 'http ALL=NOPASSWD: ALL' > /etc/sudoers.d/http
chmod 4755 /usr/bin/sudo

# update check
file=/etc/systemd/system/addons.service
echo $file
echo '[Unit]
Description=Addons Menu update check
After=network-online.target
[Service]
Type=idle
ExecStart=/srv/http/addonsupdate.sh &
[Install]
WantedBy=multi-user.target
' > $file

crontab -l | { cat; echo '00 01 * * * /srv/http/addonsupdate.sh &'; } | crontab - &> /dev/null
systemctl daemon-reload
systemctl enable addons cronie
systemctl start addons cronie

# udaclist
acards=$( redis-cli hgetall acards )
readarray -t cards <<<"$acards"
i=0
for card in "${cards[@]}"; do
	if (( i % 2 )); then
		extlabel=$( echo "$card" | awk -F '","hwplatformid'  '{print $1}' | awk -F 'extlabel":"' '{print $2}' )
		redis-cli hset udaclist "$extlabel" "$key@$extlabel" &> /dev/null
	else
		key="$card"
	fi
	(( i++ ))
done

notifysec=$( grep notify.delay /srv/http/assets/js/runeui.js | tr -dc "1-9" )
grep -q 'use_cursor yes' /root/.xinitrc && pointer=1 || pointer=''
if ! grep '^chromium' /root/.xinitrc; then
	zoomlevel=$( grep '^zoom-level' /root/.config/midori/config | sed 's/zoom-level=//' )
	browser=1
else
	zoomlevel=$( grep '^chromium' /root/.xinitrc | sed 's/.*force-device-scale-factor=\(.*\)/\1/' )
	browser=2
fi
redis-cli hmset settings notify "$notifysec" zoom "$zoomlevel" pointer "$pointer" &>/dev/null
redis-cli hset addons update 0 &>/dev/null

installfinish $@

title -nt "$info Please $( tcolor 'clear browser cache' )."

clearcache

[[ $svg == 0 ]] && restartnginx
