<?php
use app\modules\module_page_profiles\ext\Player;
$Router->map('GET|POST', 'profiles/[:id]/', 'profiles');
$Router->map('GET|POST', 'profiles/[:id]/[i:sid]/', 'profiles');
$Router->map('GET|POST', 'profiles/[:id]/[:page]/', 'profiles');
$Router->map('GET|POST', 'profiles/[:id]/[:page]/[i:sid]/', 'profiles');

$Map = $Router->match();
$server_id = $Map['params']['sid'] ?? 0;
$page = $Map['params']['page'] ?? 'info';
$profile = $Map['params']['id'];
$search = intval($_GET['search'] ?? 0);

$ban_type = [
  0 => '<div id="act" class="color-red">' . $Translate->get_translate_phrase('_Forever') . '</div>',
  1 => '<div id="ub" class="color-blue">' . $Translate->get_translate_phrase('_Unban') . '</div>',
  2 => '<div id="exp" class="color-dark"><strike>Expired</strike></div>'
];
$comms_type = [
  0 => '<div id="act" class="color-red">' . $Translate->get_translate_phrase('_Forever') . '</div>',
  1 => '<div id="ub" class="color-blue">' . $Translate->get_translate_phrase('_Uncomm') . '</div>',
  2 => '<div id="exp" class="color-dark"><strike>Expired</strike></div>'
];

if(!preg_match('^(STEAM_[0-1]:[0-1]:(\d+))|(7656119[0-9]{10})^', $Map['params']['id'])) {
  get_iframe('009', '?????? ???????? ?? ??????????') && die();
}

if (isset($_SESSION['steamid'])){
  if (empty($Map['params']['id'])) {
    header('Location: '.$General->arr_general['site'].'profiles/'.$_SESSION['steamid32'].'/?search=1/');
  }
} else {
  empty($Map) && get_iframe("404", "??????, URL ?????? ???????");
}

// ???????? ???? 'profile' ?? ???????.
empty($profile) && get_iframe('009', '?????? ???????? ?? ??????????');
empty($Db->db_data['SourceBans']) && get_iframe('012','?? ?????? ??? - SourceBans  : /storage/cache/sessions/db.php');
//empty($Db->db_data['lk']) && get_iframe('012','?? ?????? ??? - lk  : /storage/cache/sessions/db.php');

// ??????? ????????? ?????? ? ???????? ????????? Db ? ????????? Steam ID ??????.
$Player = new Player($General, $Db, $Translate, $profile, $server_id, $search);

$server_page = $Player->found[$Player->server_group]['server_group'];

// ?????? ????????? ????????.
$Modules->set_page_title($Translate->get_translate_phrase('_Player') . ': ' .  action_text_clear(action_text_trim($Player->get_name(), 20)) . ' - ' . $Player->found[$Player->server_group]['name_servers'] . ' - ' . $General->arr_general['short_name']);

// ?????? ???????? ????????.
$Modules->set_page_description($Player->found[$Player->server_group]['name_servers'] . ' - '.$Translate->get_translate_module_phrase('module_page_profiles','_Rank').': ' . $Translate->get_translate_phrase($Player->get_rank(), 'ranks_' . $Player->found[$Player->server_group]['ranks_pack']) . ', '.$Translate->get_translate_module_phrase('module_page_profiles','_Exp').': ' .  $Player->get_value());

// ?????? ??????????? ????????.
$Modules->set_page_image($General->getAvatar(con_steam32to64($Player->get_steam_32()), 1));

// ???????? ?????? ??????
$Player->set_profile_status($Translate->get_translate_phrase('_Player'), 'var(--span-color)');

if(!$Db->mysql_table_search('Core', 0, 0, "lvl_web_profiles")){
  $Db->query('Core', 0, 0, "CREATE TABLE `lvl_web_profiles`  (
    `auth` varchar(22) NOT NULL,
    `vk` text,
    `discord` text,
    `background` varchar(10) NOT NULL DEFAULT '1',
    UNIQUE INDEX `auth`(`auth`) USING BTREE
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
}

$SBAdmins = $Player->get_db_SBAdmins();
$SBSid = $Player->get_db_SBSId();
$Vips = $Player->get_db_Vips();
$SBBans = $Player->get_db_SBBans();
$SBComms = $Player->get_db_SBComms();
$Settings = $Player->get_profile_settings();
$Info = $Player->get_info();
$test = $this->lws['server_ip_explode'][0];
$back = empty($Info['background']) ? $Settings['backs']['1'] : $Settings['backs'][$Info['background']];

switch($page){
  case 'info':
    //$Shop = $Player->get_db_plugin_Shop();
    //$BattlePass = $Player->get_db_plugin_BattlePass();
    //$lcrs = $Player->get_db_plugin_lcrs();
  break;
  case 'stats':
  break;
  case 'settings':
    if (isset($_SESSION["steamid"]) && ($Player->get_steam_32() == $_SESSION['steamid32'])){
      if (isset($_POST['edit_info'])) :
        $Player->edit_info();
        echo "<meta http-equiv='refresh' content='0'>";
      endif;
    } else {
      get_iframe("404", "???????, ??? ???????? ?? ????????");
    }
  break;
  // case 'install':
  //   if(isset($_SESSION['user_admin'])){

  //   } else {
  //     get_iframe("404", "?????? ???????? ???????? ?????? ??????????????");
  //   }
  // break;
  
}