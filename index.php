<?php

$SHIPS_PER_TEAM = 14;
$NAME_MAX_LEN = 55;
$DIFF_SHIPS = 25;

$CONTROL_HUMAN	  = 0x01;
$CONTROL_AI	  = 0x02;
$AUTO_SELECT_SHIP = 0x04;
$CONTROL_NET	  = 0x08;
$AI_WEAK	  = 0x10;
$AI_GOOD	  = 0x20;
$AI_AWESOME	  = 0x40;


$shipcost = array(15, 16, 28, 30, 17,
		  11, 10, 30, 18, 19,
		  21, 23, 20,  5, 17,
		  18, 16, 13, 10,  7,
		  30, 22, 12, 23,  6, 0);

$shipnames = array("Androsyn", "Arilou", "Chenjesu", "Chmmr", "Druuge",
		   "Earthling", "Ilwrath", "Kohr-Ah", "Melnorme", "MmrnMhrm",
		   "Mycon", "Orz", "Pkunk", "Shofixti", "Slylandro",
		   "Spathi", "Supox", "Syreen", "Thraddash", "Umgah",
		   "Ur-Quan", "Utwig", "VUX", "Yehat", "ZoqFot", '');

$def_team1name = "Random Team 1";
$def_team2name = "Random Team 2";
$def_allow = str_pad('', $DIFF_SHIPS, '3');

function popup_info($msg)
{
  return ' onmouseover="show_tag(\''.$msg.'\')" onmouseout="hide_tag()"';
}

function show_ship_img($ship,$opacity=1,$onclick=NULL,$bigsize=1)
{
  global $shipnames, $shipcost, $DIFF_SHIPS;

  $wid = 34;
  $hei = 34;

  if ($bigsize) {
    $wid = $wid * 2;
    $hei = $hei * 2;
  }

  print '<img src="t'.$ship.'.png" width="'.$wid.'" height="'.$hei.'" alt=" '.$shipnames[$ship].' "';
  if ($ship >= 0 && $ship < $DIFF_SHIPS)
    print popup_info($shipnames[$ship].'<br>Cost: '.$shipcost[$ship]);

  if (isset($onclick))
    print ' onclick="'.$onclick.'"';

  if (!($opacity))
    print ' style="filter:alpha(opacity=50);-moz-opacity:.50;opacity:.50;"';

  print '>';
}

function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (int)((float) $sec + ((float) $usec * 100000));
}

function query_str($params, $sep='&amp;', $quoted=0, $encode=1)
{
  $str = '';
  foreach ($params as $key => $value) {
    $str .= (strlen($str) < 1) ? '' : $sep;
    if (($value=='') || is_null($value)) {
      $str .= $key;
      continue;
    }
    $rawval = ($encode) ? rawurlencode($value) : $value;
    if ($quoted) $rawval = '"'.$rawval.'"';
    $str .= $key . '=' . $rawval;
  }
  return ($str);
}

function phpself_querystr($querystr = null)
{
  $ret = $_SERVER['PHP_SELF'];
  $ret = preg_replace('/\/index.php$/', '/', $ret);
  if (!isset($querystr)) parse_str($_SERVER['QUERY_STRING'], $querystr);
  if (is_array($querystr)) {
    if (count($querystr)) {
      $querystr = query_str($querystr);
      if ($querystr) {
	$ret .= '?' . $querystr;
      }
    }
  } else {
    if ($querystr) {
      $ret .= '?' . $querystr;
    }
  }

  return $ret;
}


function team_cost($team)
{
  global $SHIPS_PER_TEAM, $shipcost;
  $cost = 0;
  for ($i = 0; $i < $SHIPS_PER_TEAM; $i++) {
    $cost += $shipcost[$team[$i]];
  }
  return $cost;
}

function mk_team($points, $allowed_ships, $maxships = NULL, $dupships = NULL)
{
  global $SHIPS_PER_TEAM, $DIFF_SHIPS, $shipcost;

  $team = array();
  $cost = 0;

  $teamused = array_pad(array(), count($allowed_ships), isset($dupships) ? $dupships : $SHIPS_PER_TEAM);

  if (!isset($maxships) || ($maxships < 1) || ($maxships > $SHIPS_PER_TEAM))
    $maxships = $SHIPS_PER_TEAM;

  for ($i = 0; $i < $maxships; $i++) {
    $try = 0;
    do {
      $idx = (rand() % count($allowed_ships));
      $s = $allowed_ships[$idx];
      $try++;
    } while (($cost + $shipcost[$s] > $points) && ($try < 100));

    if ($cost + $shipcost[$s] <= $points) {
      $team[$i] = $s;
      $cost += $shipcost[$s];
      $teamused[$idx]--;
      if ($teamused[$idx] < 1) {
	array_splice($allowed_ships, $idx, 1);
	array_splice($teamused, $idx, 1);
	if (count($allowed_ships) < 1) {
	  break;
	}
      }
    } else $team[$i] = $DIFF_SHIPS;
  }
  sort($team);
  $team = array_pad($team, $SHIPS_PER_TEAM, $DIFF_SHIPS);
  return $team;
}

function show_team($name, $team, $id)
{
  global $SHIPS_PER_TEAM, $NAME_MAX_LEN, $shipnames;

  print '<span class="team">';
  for ($i = 0; $i < $SHIPS_PER_TEAM; $i++) {
    show_ship_img($team[$i]);
    if ($i == ($SHIPS_PER_TEAM / 2)-1) { print "<br>\n"; }

  }
  print "<br>\n";
  print '<span class="name"><input type="text" name="t'.$id.'n" size="50" maxlength="'.$NAME_MAX_LEN.'" value="'.(($name) ? $name : 'Random Team').'" onchange="hide_urls()"></span>'."\n";
  print ' <span class="points">&nbsp;&nbsp;&nbsp;'.team_cost($team).' points</span>'."\n";
  print "</span>\n";
}

function generate_allowed_ships($allow, $num)
{
  global $DIFF_SHIPS;
  $tmpallow = array();
  for ($i = 0; $i < $DIFF_SHIPS; $i++) {
    if ((ord($allow[$i]) & $num) == $num) $tmpallow[] .= $i;
  }
  return $tmpallow;
}

function check_allow($allow)
{
  global $DIFF_SHIPS;
  $t1 = 0;
  $t2 = 0;
  for ($i = 0; $i < $DIFF_SHIPS; $i++) {
    if ((ord($allow[$i]) & 1) == 1) $t1++;
    if ((ord($allow[$i]) & 2) == 2) $t2++;
  }
  if ($t1 == 0 || $t2 == 0) {
    $c = (($t1 == 0) ? 1 : 2);
    for ($i = 0; $i < $DIFF_SHIPS; $i++) {
      $allow[$i] = ($allow[$i] | $c);
    }
  }
  return $allow;
}

function mk_random_allow()
{
  global $DIFF_SHIPS;
  $allow = '';
  for ($i = 0; $i < $DIFF_SHIPS; $i++) {
    $c = 0;
    if (rand() % 2) $c |= 1;
    if (rand() % 2) $c |= 2;
    $allow .= ''.$c.'';
  }
  return $allow;
}

function show_allowedships($teamno)
{
  global $DIFF_SHIPS, $allow;
  print '<span>';
  print "<span id=\"team".$teamno."_allowshp\">\n";
  for ($i = 0; $i < $DIFF_SHIPS; $i++) {
    if ($i && (($i % 5) == 0)) { print "<br>\n"; }
    show_ship_img($i, (ord($allow[$i]) & $teamno), 'toggle(document.f1.allow_s'.$i.'_t'.$teamno.',this);', 0);
    print '<span style="display:none;"><input type="checkbox" name="allow_s'.$i.'_t'.$teamno.'"';
    if ((ord($allow[$i]) & $teamno) == $teamno) print ' checked';
    print '></span>';
  }
  print "\n</span>\n";
  print '<br>'."\n";
  print '(<a href="" onclick="changeallowed_rnd(\'team'.$teamno.'_allowshp\');return false;">Rnd</a>';
  print '|<a href="" onclick="changeallowed_toggle(\'team'.$teamno.'_allowshp\');return false;">Toggle</a>';
  print '|<a href="" onclick="changeallowed_set(\'team'.$teamno.'_allowshp\');return false;">Set</a>)';
  print "</span>\n";
}

function binary_team($name, $team, $control)
{
  global $SHIPS_PER_TEAM, $NAME_MAX_LEN, $DIFF_SHIPS;
  if ($control >= 0)
    printf("%c", $control);

  for ($i = 0; $i < $SHIPS_PER_TEAM; $i++) {
    printf("%c", ((($team[$i] >= 0) && ($team[$i] < $DIFF_SHIPS)) ? $team[$i] : 255));
  }
  for ($i = 0; $i < $NAME_MAX_LEN; $i++) {
    if ($i < strlen($name))
      printf("%c", ord($name[$i]));
    else printf("%c", 0);
  }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['seed']))
    $seedi = $_POST['seed'];
  else
    $seedi = make_seed();

  if (isset($_POST['points']))
    $points = $_POST['points'];
  else
    $points = ((rand() % 5) * 50) + 50;

  $all_allowed = 0;
  $allow = '';
  for ($i = 0; $i < $DIFF_SHIPS; $i++) {
    $c = 0;
    if (isset($_POST['allow_s'.$i.'_t1'])) $c |= 1;
    if (isset($_POST['allow_s'.$i.'_t2'])) $c |= 2;
    $allow .= ''.$c.'';
    $all_allowed += $c;
  }
  if ($all_allowed < 5) unset($allow);

  if (isset($_POST['t1n']))
    $t1n = $_POST['t1n'];

  if (isset($_POST['t2n']))
    $t2n = $_POST['t2n'];

  if (isset($_POST['maxship']))
    $maxship = $_POST['maxship'];
  else $maxship = NULL;

  if (isset($_POST['dupships']))
    $dupships = $_POST['dupships'];
  else $dupships = NULL;

} else {
  if (!isset($_GET['seed']))
    $seedi = make_seed();
  else $seedi = $_GET['seed'];

  if (!isset($_GET['points']))
    $points = ((rand() % 5) * 50) + 50;
  else $points = $_GET['points'];

  if (isset($_GET['allow']))
    $allow = $_GET['allow'];

  if (isset($_GET['t1n']))
    $t1n = $_GET['t1n'];

  if (isset($_GET['t2n']))
    $t2n = $_GET['t2n'];

  if (isset($_GET['maxship']))
    $maxship = $_GET['maxship'];
  else $maxship = NULL;

  if (isset($_GET['dupships']))
    $dupships = $_GET['dupships'];
  else $dupships = NULL;

}

if (!isset($allow)) {
  if (isset($_GET['rndallow']) || isset($_POST['rndallow'])) {
    $allow = mk_random_allow();
    $rndallow = 1;
  } else {
    $allow = $def_allow;
  }
}

if (strlen(trim($seedi)) < 1)
  $seedi = make_seed();

if (isset($dupships) && (($dupships < 1) || ($dupships > $SHIPS_PER_TEAM)))
  $dupships = NULL;

srand($seedi);

$allow = check_allow($allow);

$team1 = mk_team($points, generate_allowed_ships($allow, 1), $maxship, $dupships);
$team2 = mk_team($points, generate_allowed_ships($allow, 2), $maxship, $dupships);

$team1name = (isset($t1n)) ? substr($t1n,0,$NAME_MAX_LEN) : $def_team1name;
$team2name = (isset($t2n)) ? substr($t2n,0,$NAME_MAX_LEN) : $def_team2name;



if (isset($_GET['download'])) {

  if ($_GET['download'] == 3) {

    header('Content-Type: binary/octet-stream');
    header('Content-Length: 140');
    header('Content-Disposition: attachment; filename="melee.cfg"');

    binary_team($team1name, $team1, $CONTROL_HUMAN);
    binary_team($team2name, $team2, $CONTROL_AI|$AI_GOOD|$AUTO_SELECT_SHIP);
    exit;
  } else if ($_GET['download'] == 2) {
    header('Content-Type: binary/octet-stream');
    header('Content-Length: 69');
    header('Content-Disposition: attachment; filename="'.$team2name.'.mle"');

    binary_team($team2name, $team2, -1);
    exit;
  } else if ($_GET['download'] == 1) {
    header('Content-Type: binary/octet-stream');
    header('Content-Length: 69');
    header('Content-Disposition: attachment; filename="'.$team1name.'.mle"');

    binary_team($team1name, $team1, -1);
    exit;
  }

}

$dat = array('seed'=>$seedi, 'points'=>$points);
if ($allow != $def_allow) $dat['allow'] = $allow;
if ($team1name != $def_team1name) $dat['t1n'] = $team1name;
if ($team2name != $def_team2name) $dat['t2n'] = $team2name;
if (isset($maxship) && ($maxship > 0) && ($maxship < $SHIPS_PER_TEAM)) $dat['maxship'] = $maxship;
if (isset($dupships)) $dat['dupships'] = $dupships;

header('Content-type: text/html; charset=iso-8859-1');

print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
print "\n<html>\n";
print "<head>\n";
print "<title>Ur-Quan Masters Random Team Generator</title>\n";
print '<link rel="icon" href="http://bilious.homelinux.org/~paxed/uqmteam/urquan.png">';
print "</head>\n";
print "<body>\n";
print "<h1><a href=\"http://sc2.sf.net\">The Ur-Quan Masters</a> Random Team Generator</h1>\n";
print '<form method="POST" action="'.phpself_querystr().'" name="f1">'."\n";

print '<div>'."\n";

print "<div>\n";

print '<div>'."\n";
 print '<div style="float:left;padding-right:2em;">'."\n";
  show_team($team1name, $team1, 1);
  $dat['download'] = 1;
  print "\n";
  print '<br><A class="volatile" href="'.phpself_querystr($dat).'">Download this team</a>'."\n";
 print "</div>\n";
 show_allowedships(1);

 print "<br><br>\n";

 print '<div style="float:left;padding-right:2em;">'."\n";
  show_team($team2name, $team2, 2);
  $dat['download'] = 2;
  print "\n";
  print '<br><A class="volatile" href="'.phpself_querystr($dat).'">Download this team</a>'."\n";
 print "</div>\n";
 show_allowedships(2);
print "</div>\n";


#print "<br><br>\n";
$dat['download'] = 3;
print '<A class="volatile" href="'.phpself_querystr($dat).'">Download both teams as melee.cfg</a>'."\n";

unset($dat['download']);

print "</div>\n";

print "<div style=\"padding-top:1em;\">\n";

#print "<hr>\n";
print "<table>\n";
print '<tr><td>Seed:</td><td><input type="text" name="seed" size="16" maxlength="32" value="'.$seedi.'" onchange="hide_urls()"></td></tr>'."\n";
print '<tr><td>Points:</td><td><input type="text" name="points" size="6" maxlength="6" value="'.$points.'" onchange="hide_urls()"></td></tr>'."\n";
print '<tr><td>Max ships:</td><td><input type="text" name="maxship" size="4" maxlength="2" value="'.$maxship.'" onchange="hide_urls()"></td></tr>'."\n";
print '<tr><td>Max duplicate ships:</td><td><input type="text" name="dupships" size="4" maxlength="2" value="'.$dupships.'" onchange="hide_urls()"></td></tr>'."\n";

/*print '<br>Random allowed ships: <input type="checkbox" name="rndallow"'.(isset($rndallow) ? ' checked' : '').'>';*/

print '<tr><td></td><td><input type="Submit" value="Update Teams"><span id="press_here"></span></td></tr>'."\n";
print "</table>\n";
#print "<hr>\n";


print '<br><A class="volatile" href="'.phpself_querystr($dat).'">Direct URL to these teams</a>'."\n";
print "<br>\n";
print '<br><A href="'.phpself_querystr(array(''=>0)).'">Generate random</a>';
unset($dat['seed']);
print "\n";
print '<br><A class="volatile" href="'.phpself_querystr($dat).'">Generate random with current settings</a>'."\n";

print "<br>\n";

print "</div>\n";

print "</div>\n";

print "</form>\n";

print '<span id="extra_info" style="position:absolute;background:black;color:white;padding:5px;visibility:hidden;"></span>'."\n";
print '<script src="uqm.js" type="text/javascript"></script>'."\n";
print "</body>\n</html>";

?>