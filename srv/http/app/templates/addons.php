<?php
include '/srv/http/addonslist.php';

$total = exec( "/usr/bin/sudo /usr/bin/sfdisk -l /dev/mmcblk0 | head -n 1 | cut -d' ' -f5" );
$unpart = exec( "/usr/bin/sudo /usr/bin/sfdisk -F | grep mmcblk0 | cut -d' ' -f6" );
$mbtotal = round( $total / pow( 2, 20 ) );
$mbunpart = round( $unpart / pow( 2, 20 ) );
$mbfree = round( disk_free_space( '/' ) / pow( 2, 20 ) );
$wtotal = 170;
$wunpart = round( ( $mbunpart / $mbtotal ) * $wtotal );
$wfree = round( ( $mbfree / $mbtotal ) * $wtotal );
$wused = $wtotal- $wfree - $wunpart;
$htmlfree = $wfree ? '<p id="diskfree" class="disk" style="width: '.$wfree.'px;">&nbsp;</p>' : '';
$available = '<white>'.( $mbfree < 1024 ? $mbfree.' MiB' : round( $mbfree / 1024, 2 ).' GiB' ).'</white> free';

if ( $mbunpart < 10 ) {
	$redis->hSet( 'addons', 'expa', 1 );
	$htmlunpart = '';
	$expandable = '';
} else {
	$htmlunpart = '<p id="diskunpart" class="disk" style="width: '.round( ( $mbunpart / $mbtotal ) * $wtotal ).'px;">&nbsp;</p>';
	$expandable = ' ● <a>'.( $mbunpart < 1024 ? $mbunpart.' MiB' : round( $mbunpart / 1024, 2 ).' GiB' ).'</a> expandable';
}
echo '
<div class="container">
	<a id="close" class="close-root" href="/"><i class="fa fa-times"></i></a>
	<h1><i class="fa fa-addons"></i>&ensp;Addons</h1>
	<p class="bl"></p>
	<p id="diskused" class="disk" style="width: '.$wused.'px;">&nbsp;</p>'.$htmlfree.$htmlunpart.'
	<p id="disktext" class="disk">&ensp;'.$available.$expandable.'</p>
	<p id="issues" class="disk" href="http://www.runeaudio.com/forum/addons-menu-install-addons-the-easy-way-t5370-1000.html" target="_blank">issues&ensp;<i class="fa fa-external-link"></i>
	</p>
';
// ------------------------------------------------------------------------------------
$list = '';
$blocks = '';
// sort
$arraytitle = array_column( $addons, 'title' );
$addoindex = array_search( 'Addons Menu', $arraytitle );
$arraytitle[ $addoindex ] = 0;
$updatecount = 0;
array_multisort( $arraytitle, SORT_NATURAL | SORT_FLAG_CASE, $addons );
$arrayalias = array_keys( $addons );
foreach( $arrayalias as $alias ) {
	$addon = $addons[ $alias ];
	
	// hide by conditions
	if ( $addon[ 'hide' ] === 1 ) continue;
	
	$thumbnail = isset( $addon[ 'thumbnail' ] ) ? $this->asset( $addon[ 'thumbnail' ] ) : '';
	if ( isset( $addon[ 'buttonlabel' ] ) ) {
		$buttonlabel = $addon[ 'buttonlabel' ];
	} else {
		$buttonlabel = '<i class="fa fa-plus-circle"></i>Install';
	}
	
	if ( $redisaddons[ $alias ] || $redis->hGet( 'addons', $alias ) ) {
		$check = '<i class="fa fa-check status"></i> ';
		if ( !isset( $addon[ 'version' ] ) 
			|| $addon[ 'version' ] == $redisaddons[ $alias ] ) {
			// !!! mobile browsers: <button>s submit 'formtemp' with 'get' > 'failed', use <a> instead
			$btnin = '<a class="btn btn-default disabled">'.$buttonlabel.'</a>';
		} else {
			$updatecount++;
			$check = '<i class="fa fa-refresh status"></i> ';
			$btnin = '<a class="btn btn-primary" alias="'.$alias.'"><i class="fa fa-refresh"></i>Update</a>';
		}
		$btnunattr = isset( $addon[ 'rollback' ] ) ?' rollback="'.$addon[ 'rollback' ].'"' : '';
		$btnun = '<a class="btn btn-default" alias="'.$alias.'"'.$btnunattr.'><i class="fa fa-minus-circle"></i>Uninstall</a>';
	} else {
		$check = '';
		$needspace = isset( $addon[ 'needspace' ] ) ? $addon[ 'needspace' ] : 1;
		$attrspace = $needspace < $mbfree ? '' : ' needmb="'.$needspace.'" space="'.$available.$expandable.'"';
		$conflict = isset( $addon[ 'conflict' ] ) ? $addon[ 'conflict' ] : '';
		$conflictaddon = $conflict ? $redis->hget( 'addons', $conflict ) : '';
		$attrconflict = !$conflictaddon ? '' : ' conflict="'.preg_replace( '/ *\**$/', '', $addons[ $conflict ][ 'title' ] ).'"';
		$attrdepend = '';
		if ( isset( $addon[ 'depend' ] ) ) {
			$depend = $addon[ 'depend' ];
			$dependaddon = $redis->hget( 'addons', $depend );
			if ( !$dependaddon ) $attrdepend = ' depend="'.preg_replace( '/ *\**$/', '', $addons[ $depend ][ 'title' ] ).'"';
		}
		$btnin = '<a class="btn btn-default" alias="'.$alias.'"'.$btninclass.$attrspace.$attrconflict.$attrdepend.'>'.$buttonlabel.'</a>';
		$btnun = '<a class="btn btn-default disabled"><i class="fa fa-minus-circle"></i>Uninstall</a>';
	}
	
	// addon list ---------------------------------------------------------------
	$title = $addon[ 'title' ];
	// hide Addons Menu in list
	if ( $alias !== 'addo' ) {
		if ( substr( $title, -1 ) === '*' ) {
			$last = array_pop( explode( ' ', $title ) );
			$listtitle = preg_replace( '/\**$/', '', $title );
			$star = '&nbsp;<a>'.str_replace( '*', '★', $last ).'</a>';
		} else {
			$listtitle = $title;
			$star = '';
		}
		if ( $check === '<i class="fa fa-refresh status"></i> ' ) $listtitle = '<blue>'.$listtitle.'</blue>';
		$list .= '<li alias="'.$alias.'" title="Go to this addon">'.$check.$listtitle.$star.'</li>';
	}
	// addon blocks -------------------------------------------------------------
	$version = isset( $addon[ 'version' ] ) ? $addon[ 'version' ] : '';
	$revisionclass = $version ? 'revision' : 'revisionnone';
	$revision = str_replace( '\\', '', $addon[ 'revision' ] ); // remove escaped [ \" ] to [ " ]
	$revision = '<li>'.str_replace( '<br>', '</li><li>', $revision ).'</li>';
	$description = str_replace( '\\', '', $addon[ 'description' ] );
	$sourcecode = $addon[ 'sourcecode' ];
	if ( $sourcecode && $addon[ 'buttonlabel' ] !== 'Link' ) {
		$detail = '&emsp;<a href="'.$sourcecode.'" target="_blank">detail&ensp;<i class="fa fa-external-link"></i></a>';
	} else {
		$detail = '';
	}
	$blocks .= '
		<div id="'.$alias.'" class="boxed-group">';
	if ( $thumbnail ) $blocks .= '
		<div style="float: left; width: calc( 100% - 110px);">';
	$blocks .= '
			<legend title="Back to top">'
				.$check.'<span>'.preg_replace( '/\**$/', '', $title ).'</span>
				&emsp;<p><a class="'.$revisionclass.'">'.$version.( $version ? '&ensp;<i class="fa fa-chevron-down"></i>' : '' ).'</a>
				&ensp;by<white>&ensp;'.$addon[ 'maintainer' ].'</white></p><i class="fa fa-arrow-up"></i>
			</legend>
			<ul class="detailtext" style="display: none;">'
				.$revision.'
			</ul>
			<form class="form-horizontal" alias="'.$alias.'">
				<p class="detailtext">'.$description.$detail.'</p>';
	if ( $alias !== 'addo' ) $blocks .= $version ? $btnin.' &nbsp; '.$btnun : $btnin;
	$blocks .= '
			</form>';
	if ( $thumbnail ) $blocks .= '
		</div>
		<img src="'.$thumbnail.'" class="thumbnail">
		<div style="clear: both;"></div>';
	$blocks .= '
		</div>';
}
$redis->hSet( 'addons', 'update', $updatecount );
$redis->hSet( 'display', 'update', $updatecount );
// ------------------------------------------------------------------------------------
echo '
	<ul id="list">'.
		$list.'
	</ul>
';
echo $blocks;
?>
</div>
<p id="bottom"></p> <!-- for bottom padding -->
<input id="addonswoff" type="hidden" value="<?=$this->asset('/fonts/addons.woff')?>">
<input id="addonsttf" type="hidden" value="<?=$this->asset('/fonts/addons.ttf')?>">
<?php
$keepkey = array( 'title', 'installurl', 'rollback', 'option' );
foreach( $arrayalias as $alias ) {
	if ( $alias === 'addo' || $alias === 'dual' ) continue;
	$addonslist[ $alias ] = array_intersect_key( $addons[ $alias ], array_flip( $keepkey ) );
}
?>
<input id="addonslist" type="hidden" value='<?=json_encode( $addonslist )?>'>
