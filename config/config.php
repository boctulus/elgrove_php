<?php

require_once 'constants.php';

setlocale(LC_ALL, 'es_AR.UTF-8');

return [
	#
	# For a sub-foder in /var/www/html just set as
	# BASE_URL' => /folder/'
	#
	'BASE_URL' => '/',   
	'HTTPS' => 'Off',
	'DEFAULT_CONTROLLER' => 'HomeController',

	'database' => [
		'host' => 'localhost',
		'db_name' => 'elgrove', 
		'user' => 'elgrove', 
		'pass' => 'jUm(_8._33A2'
	], 

	'DateTimeZone' => 'America/Argentina/Buenos_Aires',

	'debug_mode'   => true,

	'access_token' => [
		'secret_key' =>'/`D*x!I<T^SH*_~&<#-&^%s~etN,RX`G_|{<+#"-I<{!}*![[}${([-zC<~pX$,e~#[[h~nyW?~:`ak><_b@>@=|$o=?h}!u+U&[##/\(> []T.Yx_J\x|g{\N`h^})\_a/<D#X( m+qb#|-,i>-~.j~(RG&[_*.,`r^LM,.E<V:`v~?;`~#p&<:W;>\%\]~fE}d~m{!u@,"Jt<b-?}A=m]H$-`|[B&@<.u@FAl:u}@>|ft!?|&|@|=@aTC@v\|Oe Gn|Rg}}; !@\@D+~@.;~<V[&yno^U|>{?{:vc`^[S`W?V<E<|[;}]}{|-{o["|}E[Op&$yL%+*}G}(|]..?,w}!#P+,=a(+`<<*^N.:V#$%.lr(%:!|&zM#%F?";=]ABb.;/[xd)#{^J]!~~|){[>a:*]>`%-"~\Fu}LBUW_},J[+,a$(? G,#" |$}VTS%*}K(|[_&:gm%^I/z+[M_E<(.n|j#$-<|]${*{+$[b_*/}m$m^&T^%>[^&!]|k+L',
		'expiration_time' => 60 * 15 * 100000,   // seconds (normalmente 60 * 15)
		'encryption' => 'HS256'			
	],

	'refresh_token' => [
		'secret_key' => '^~~W?]]t@U|~yKi`b$;:#"F(HD`@K:[~|>d}{o%&{^M^(>d (?]~H@!$ #$}(]%,z~+#^_|b~eD.?hgb],w/E.;$$-(]~\h*)+"N^{,uWFT,!L&=%Y[)[?}p;r}!`/i`BJ?c]]"~&^w!_*XYD-!|.]-`[)R!)x$^=`Y>A`,IR~;|>q]//nPh};;"h>S@p^#)/j}Q^+]&>[F{;J,%&%{y:w|<A]&s[,:.|%?djk=<uZe;-(;}rg:J~|[:oF^.{|R;<wo){+[!H\~*|`V~[G~$gZ~)|K|+)lr[%>$_%>{)\)`~C" ==n#?eH:;&moG,}=|[(:P;;:&|_}tmuZ/W\/o:\&)];~>|]}y\,o-Mm|@;<hX>([?W_};#%@$!y{C(r~,&=]+%.?_?A%!f}=VX$|@*Iu:?<(A/^S\}L|=${$_*P)^"qtetg`~`|fC)K^/%/-s&W e]l}T{:M|{{Z#~/Um*.s$"^&^)NV},!> &"[O&\)?>cv(&#U|||l=~W"{]\$^',
		'expiration_time' => 315360000,   // seconds
		'encryption' => 'HS256'	
	],

	'method_override' => [
		'by_url' => true,
		'by_header' => true
	],

	/* 
		aditional role to the implicit 'registered' after being registered
		leave empty for none
	*/
	//'registration_role' => 'copropietario',

	// seconds
	'email' => [
		'secret_key' => 'TbD:||:"%;(]]I{Q[*Q"[}=J`.~z#j*.-Vt"]*!~>#k}`!~^^%[?>.T_}] }@:<|=/{]y~[^ @)?WV^)+c$"l+&@.\?Nx~$_Gx=%_=Lu:&?!~\{{?%*?}IV~@:d:|][:/;luvS"*h{"n^\]/?[:@(:SM+~~)$vh\%_Q:[[M(~xx.)%|}),c,{$gw#{~h>:@-B|_`(L~\%:[r]$=`+:]St#!}%#@|?{[m@;("[^!Y_TbSNl-k{.}.vO:)"`}:|%G:/+P$fG(W>G[\|="z`||~fC+kLe[~+E~}}#`B>: }d"\Z)R}f@Y&X..d{/px~~_zc]+{d]##|a$M@,P>~U`A!CR*:!`~?)|\mVB!|+ uQ*l*\;|*_zc"*d}+q;s{@C()V$vIv*=B[{$ `S!&+`_t;{u:&_ `DU|BD@|;"NS.)>+^&@ssm\^%#h+\{{&fnN@![%#@/[F.>),PT\i~n|^$~$&I\;=;U}"N.(LI&{m&o&S >X`$-<|td~-Kyx].h?/O]',
		'expires_in' => 7 * 24 * 3600,
		'encryption' => 'HS256',
		'mailer' =>  [	
			'from'	 => ['no_responder@simplerest.mapapulque.ro', 'No responder'],	
			'object' => [
				'Host' => 'smtp.easyname.com',
				'Username' => '162997mail6',
				'Password' => 'Hvr0tf9Is#',
				'Port' => 587,
	            'SMTPAuth' => true,
				'SMTPSecure' => 'ssl',
				'SMTPDebug' => 4,
				'CharSet' => 'UTF-8',
				'Debugoutput' => 'html',
				'SMTPSecure' => false
			]
		]
	],

	'pretty' => false,	
	
	'paginator' => ['max_limit' => 50,
					'default_limit' => 10
	],

	'google_auth'  => [
		'client_id' => 'xxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com',
		'client_secret' => 'xxxxxxxxxxxxxxxxxxxx',
		// https://simplerest.mapapulque.ro/login/google_login
		'callback' => 'http://xxxxxxxxxx/login/google_login'
	],

	'facebook_auth' => [
		'app_id' => '00000000000000',
		'app_secret' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxx', 
		'callback' => 'https://xxxxxxxxx/login/fb_login'
	]
	
];