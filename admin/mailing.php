<?php
/**
 *  Allomani Audio & Video (Songs) v3.0
 * 
 * @package Allomani.Songs
 * @version 3.0
 * @copyright (c) 2006-2018 Allomani , All rights reserved.
 * @author Ali Allomani <info@allomani.com>
 * @link http://allomani.com
 * @license GNU General Public License version 3.0 (GPLv3)
 * 
 */

chdir('./../');
define('CWD', (($getcwd = getcwd()) ? $getcwd : '.'));
define('IS_ADMIN', 1);
$is_admin =1 ;

include_once(CWD . "/global.php") ;

if(!check_admin_login()){die("<center> $phrases[access_denied] </center>");} 

if_admin("members");

print "<html dir=$global_dir>\n";
print "<META http-equiv=Content-Language content=\"$settings[site_pages_lang]\">
<META http-equiv=Content-Type content=\"text/html; charset=$settings[site_pages_encoding]\">";

print "<link href=\"images/style.css\" type=text/css rel=stylesheet>
<script src='$scripturl/js/prototype.js'></script>
<script src='javascript.js' type=\"text/javascript\" language=\"javascript\"></script>";


if ($conf){
if($datastring){$mailing=unserialize(base64_decode($datastring));}

if(is_array($mailing)){extract($mailing);}
                     
                               
if($datastring){$start = intval($start)+intval($perpage);$mailing['start']=$start;unset($datastring);  }

     $start = intval($start);
     $perpage = intval($perpage);

if($send_to=="all"){

       $qr = db_query("select ".members_fields_replace("id").",".members_fields_replace("username").",".members_fields_replace("email").",".members_fields_replace('pm_email_notify')." from ".members_table_replace("songs_members")." order by ".members_fields_replace("id")." limit $start,$perpage",MEMBER_SQL);
        }else{
        $qr = db_query("select ".members_fields_replace("id").",".members_fields_replace("username").",".members_fields_replace("email").",".members_fields_replace('pm_email_notify')." from ".members_table_replace("songs_members")." where ".members_fields_replace("username")." like '".db_escape($username,false)."'",MEMBER_SQL);
        }


         if(db_num($qr)){

             $count_dt = db_qr_fetch("select count(".members_fields_replace("id").") as count from ".members_table_replace("songs_members").iif($send_to!="all"," where binary ".members_fields_replace("username")."='".db_escape($username,fase)."'")." order by ".members_fields_replace("id"),MEMBER_SQL);
         $data_count = $count_dt['count'];
         unset($count_dt);


        

          if($op=="msg"){
 while($data = db_fetch($qr)){
    
               print "<li> $phrases[cp_mailing_sending_to] $data[username] .. " ;
         data_flush();
        if(!$from_subject){$from_subject = "$phrases[without_title]";}

      
        db_query("insert into songs_members_msgs (owner,uid,username,title,content,date) values('$data[id]','0','".db_escape($from_name)."','".db_escape($from_subject)."','".db_escape($from_msg)."','".time()."')");
      
        print "<font color=green><b>$phrases[done] </b></font></li>";
        
      
        //---------- send email to receiver ------------
                    if($data['pm_email_notify']){
                      $nmsg_url = "$scripturl/messages.php";
                      $nmsg = get_template("pm_email_notify_msg",array("{name_from}","{url}"),array($from_name,$nmsg_url));
                      $email_result = send_email($sitename,$mailing_email,$data['email'],"$phrases[pm_email_notify_subject]",$nmsg);
                    }
                    
                      
               data_flush();
}
          }else{

 while($data = db_fetch($qr)){
      print "<li> $phrases[cp_mailing_sending_to] $data[username] .. " ;
         data_flush();
      $mailResult =  send_email($from_name,$from_email,$data['email'],$from_subject,$from_msg);

    if($mailResult){
     print "<font color=green><b>$phrases[done] </b></font></li>";
     }else{
      print "<font color=red><b> $phrases[failed] </b></font></li>";
      }
      data_flush();
      }

          }
               


               if(($start+$perpage) < $data_count){
   print "<br><br>
   <form action='mailing.php' method=post name='mailing_form'>
          <input type=hidden name=conf value='1'>
           <input type=hidden name=datastring value=\"".base64_encode(serialize($mailing))."\">
           <input type=submit value=' $phrases[next_page] '>
           </form>";
           if($auto_pageredirect){
           print "<script>mailing_form.submit();</script>";
           }

   }else{
      print "<br><br> <font size=4> $phrases[process_done_successfully] </font>" ;
   }


                 }else{
                         print "<center>  $phrases[no_results] </center>";
                         }


        }else{


   print "<center>


   <form action='mailing.php' method=post>
   <input type=hidden name=conf value='1'>
   <table width=80% class=grid>
    <tr><td>$phrases[cp_send_as] </td><td>
    <select name=\"mailing[op]\" id=\"mailing[op]\" onChange=\"show_snd_mail_options2()\">
    <option value='email'> $phrases[cp_as_email] </option>
    <option value='msg'> $phrases[cp_as_pm] </option>
    </select>
    </td></tr>
     ";

     if($username){$chk="selected";}
     print"
     <tr><td>$phrases[cp_send_to]  </td><td>
    <select name=\"mailing[send_to]\"  id=\"mailing[send_to]\" onChange=\"show_snd_mail_options()\">
    <option value='all'> $phrases[all_members] </option>
    <option value='one' $chk> $phrases[one_member] </option>
    </select>
    </td></tr>

   " ;
   if($username){
    print "<tr id='when_one_user_email'>";
    }else{
    print "<tr id='when_one_user_email' style=\"display: none; text-decoration: none\">";
  }
    print "<td>$phrases[cp_username]</td><td>
    <input type=text name='mailing[username]'  value='$username' size=30></td></tr>

   <tr ><td> $phrases[sender_name] </td><td><input type=text name='mailing[from_name]' value='$sitename' size=30></td></tr>
   
    <tr id='sender_email_tr' with='100%'><td> $phrases[sender_email] </td><td>

     <input type=text name='mailing[from_email]' value=\"$mailing_email\" size=30 dir='ltr'></td></tr>

     <tr><td> $phrases[msg_subject] </td><td><input type=text name='mailing[from_subject]' size=30></td></tr>
    <tr><td>  $phrases[the_message] </td><td><textarea name='mailing[from_msg]' cols=50 rows=20></textarea></td></tr>

    <tr><td>$phrases[start_from] </td><td>
    <input type=text name='mailing[start]'  value='0' size=2></td></tr>

    <tr><td>$phrases[mailing_emails_perpage]</td><td>
    <input type=text name='mailing[perpage]'  value='30' size=2></td></tr>

    <tr><td>$phrases[auto_pages_redirection]</td><td>
    <select name='mailing[auto_pageredirect]'>
    <option value=0>$phrases[no]</option>
    <option value=1>$phrases[yes]</option>
    </select>
    </td></tr>

    <tr><td colspan=2 align=center><input type=submit value=' $phrases[send] '></td></tr></table></center>";
}


?>
