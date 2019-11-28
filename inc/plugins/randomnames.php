<?php

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("misc_start", "randomnames_misc");

function randomnames_info()
{
	return array(
		"name"			=> "Namenswürfel",
		"description"	=> "Erweitert das Forum um eine Extraseite, auf der zufällige Namen für Charaktere ausgegeben werden können.",
		"website"		=> "https://github.com/its-sparks-fly",
		"author"		=> "sparks fly",
		"authorsite"	=> "https://github.com/its-sparks-fly",
		"version"		=> "1.0",
		"compatibility" => "18*"
	);
}

function randomnames_activate()
{
	global $db;
	$misc_randomnames = array(
        'title'        => 'misc_randomnames',
        'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\'bbname\']} - {$lang->randomnames}</title>
            {$headerinclude}</head>
        <body>
            {$header}
                <table width="100%" cellspacing="5" cellpadding="5">
                    <tr>
                        <td valign="top">
                            <div class="thead">{$lang->randomnames}</div>	
							<form method="get" id="randomnames">
								<input type="hidden" name="action" value="randomnames" />
								<table class="tborder" cellspacing="5" cellpadding="5" style="width: 80%;">
									<tr>
										<td class="tcat" width="33%">{$lang->randomnames_type}</td>
										<td class="tcat" width="33%">{$lang->randomnames_gender}</td>
										<td class="tcat" width="33%">{$lang->randomnames_letter}</td>
									</tr>	
									<tr>
										<td class="trow2" align="center" rowspan="2">
											<select name="type">
												{$nametype_bit}
											</select>
										</td>
										<td class="trow2" align="center" rowspan="2">
											<select name="gender">
												{$gender_bit}
											</select><br />
											<span class="smalltext">{$lang->randomnames_gender_alert}</span>
										</td>
										<td class="trow2" align="center">
											<span class="smalltext">{$lang->randomnames_prename}</span>
											<select name="letter">
												<option value="">{$lang->randomnames_pick}</option>
												{$letter_bit}
											</select>
										</td>
									</tr>
									<tr>
										<td class="trow2" align="center">
											<span class="smalltext">{$lang->randomnames_surname}</span>
											<select name="last_letter">
												<option value="">{$lang->randomnames_pick}</option>
												{$last_letter_bit}
											</select>
										</td>
									</tr>
									<tr>
										<td colspan="3" class="trow2" align="center">
											<input type="submit" value="{$lang->randomnames_generate}" \>
										</td>
									</tr>								
								</table>
							</form>
							
							<br ><br />
							<center>
								<span style="font-size: 22px;">{$randomname}</span><br />
							</center>
                        </td>
                    </tr>
                </table>
            {$footer}
        </body>
    </html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $misc_randomnames);
}

function randomnames_deactivate()
{
	global $db;
	$db->delete_query("templates", "title LIKE '%randomnames%'");
}

function randomnames_misc() 
{
    global $mybb, $lang, $templates, $headerinclude, $header, $footer; 
    $lang->load('randomnames');
    $mybb->input['action'] = $mybb->get_input('action');

    if($mybb->input['action'] == "randomnames") {

        $gender = $mybb->input['gender'];
        $type = $mybb->input['type'];
        $letter = $mybb->input['letter'];
        $last_letter = $mybb->input['last_letter'];

        $alphabet = range('A', 'Z');

        $genders = [
            "male" => "männlich",
            "female" => "weiblich"
        ];

        $nametypes = [
            "prenames" => "Vorname",
            "lastnames" => "Nachname",
            "both" => "beides"
        ];

        foreach($alphabet as $alphabetletter) {
            $selected = "";
            if($alphabetletter == $letter) {
                $selected = "selected";
            }
            $letter_bit .= "<option value=\"{$alphabetletter}\" {$selected}>{$alphabetletter}</option>";
        }
		
        foreach($alphabet as $alphabetletter) {
            $selected = "";
            if($alphabetletter == $last_letter) {
                $selected = "selected";
            }
            $last_letter_bit .= "<option value=\"{$alphabetletter}\" {$selected}>{$alphabetletter}</option>";
        }

        foreach($genders as $genderkey => $gendername) {
            $selected = "";
            if($gender == $genderkey) {
                $selected = "selected";
            }            
            $gender_bit .= "<option value=\"{$genderkey}\" {$selected}>{$gendername}</option>";
        }

        foreach($nametypes as $namekey => $nametype) {
            $selected = "";
            if($type == $namekey) {
                $selected = "selected";
            }   
            $nametype_bit .= "<option value=\"{$namekey}\" {$selected}>{$nametype}</option>";
        }

        switch($type) {
            case "lastnames":
                $letter = $last_letter;
                break;
            case "prenames":
                $type = $gender;
                break;
            case "both":
				$type = $gender;
                $lastnames = explode("\n", file_get_contents('src/lastnames.txt'));
                if(!empty($last_letter)) {
                    $lastnames = preg_grep('/^'.$last_letter.'.*/', $lastnames);
                }
                $namesIndex = array_rand($lastnames);
                $randomlastname = "{$lastnames[$namesIndex]}";
				break;
			case "":
                $type = "male";
                break;
        }

        $names = explode("\n", file_get_contents('src/'.$type.'.txt'));
		
        if(!empty($letter)) {
			$names = preg_grep('/^'.$letter.'.*/', $names);
        }
        
		$namesIndex = array_rand($names);
        $randomname = "{$names[$namesIndex]} ";
        $randomname .= $randomlastname;


		eval("\$page = \"".$templates->get("misc_randomnames")."\";");
		output_page($page);
    }
}
