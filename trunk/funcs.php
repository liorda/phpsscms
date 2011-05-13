<?php

/* main function - generate page according to the GET request parameters */
function GeneratePage()
{
	$s = $_GET['s'];
	$c = $_GET['c'];

	if (!isset($s) && !isset($c)) {
	$s = "index";
	}
	if (isset($s))
		ValidifyCnS($s);
	if (isset($c))
		ValidifyCnS($c);

	/* load site's sturcture */
	//$xml = simplexml_load_file('struct.xml');
	$xml = simplexml_load_file('sample_rtl_struct.xml');

	/* load language data */
	InitLocalization($xml->metadata->locale);

	list($top_str, $r) = TopMenu($xml->sections, $s);
    
	$top_str .= "\n" . '<div id="main">' . "\n\n";
    
	list($side_str, $t) = SideMenu($xml->sections, $s, $r, $c);

	$header_str = HeaderStr($xml, $r, $t);
    
	$content_str = ContentStr($xml->sections, $r, $t);
    
	$footer_str = FooterStr($xml);

	echo $header_str;
	echo $top_str . $side_str . $content_str . $footer_str;
}

/* start the php-gettext extention */
function InitLocalization($locale)
{
	/* make sure your system support the required locales by running `locale -a` */
	if (isSet($_GET["locale"])) $locale = $_GET["locale"];
	putenv("LC_ALL=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages", "./locale");
	textdomain("messages");
}
/* s and c should be english small characters only! die if not: */
function ValidifyCnS($str)
{
  /* die if found " ", """, "'", "<", ">", "/", "\" or "." */
	if (preg_match("/\ /", $str) || preg_match('/\\"/', $str) || preg_match('/\</', $str) ||
		preg_match('/\>/', $str) || preg_match('/\\\/', $str) || preg_match('/\\//', $str) ||
		preg_match('/\\./', $str)   ) {
			// Abort the script
			die("Invalid request");
	}
}

function ContentStr($xml, $r, $t)
{
	if (isset($t) && isset($r)) {
		$str = $xml->section[$r]->chapters->chapter[$t]->data;
		$title = $xml->section[$r]->chapters->chapter[$t]->title;
	}
	elseif (isset($r)) {
		$str = $xml->section[$r]->data;
		$title = $xml->section[$r]->title;
	}
	else {
		$str = '<br /><br /><div align="center">' . _("Page was not found")
		. '</div><br /><br />';
		$title = _("Page was not found");
	}
    
	return '<div id="text">' . "\n" . '<h1>' . $title . "</h1>\n" . $str . "</div>\n\n";
}

function TopMenu($xml, $s)
{
	$i = -1;
	$str = "\n\n" . '<div id="menu">' . "\n";
	foreach ($xml->section as $section) {
		$i++;
		$title = $section->title;
		$suid = $section->suid;
		$str .= '<a';
		if ($suid == $s) {
			$str .= ' class="selected"';
			$j=$i;
		}
		$str .= ' href="?s=' . $suid;
		$str .= '">' . $title . "</a>&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	$str .= "\n</div>\n";
	return array($str,$j);
}

function SideMenu($xml, $s, $r, $c)
{
    $str = '<div id="sidebar">' . "\n";
    
    $i=-1;
    if (isset($r)) {
	$str .= "<ul>\n";
        foreach ($xml->section[$r]->chapters->chapter as $chapter) {
            $i++;
            $title = $chapter->title;
            $cuid = $chapter->cuid;
            $str .= "<li";
            if ($c == $chapter->cuid) {
                $str .=' class="selected"';
                $j=$i;
            }
            $str .= '><a href="?s=' . $s . '&amp;c=' . $cuid . '">';
            $str .= $title . "</a></li>\n";
        }
	$str .= "</ul>\n";
    }
    $str .= "</div>\n\n";
    return array($str, $j);
}

function HeaderStr($xml, $r, $t)
{
    $str =  '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n" .
            '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n" .
            "<head>" . 
            '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n" . 
            "<title>TITLE_TOKEN</title>\n" . 
            '<link rel="stylesheet" type="text/css" href="' . $xml->metadata->template->relpath . '/style.css"/>' . "\n" .
            "</head>\n\n" . 
            "<body>\n\n" .
            '<div id="container">' . "\n\n" .
            '<div id="header-main"><a href="' . $xml->metadata->basedir . '">' . $xml->metadata->headline . '</a></div>' . "\n" .
            '<div id="header-sub"><a href="' . $xml->metadata->basedir . '">' . $xml->metadata->subheadline . '</a></div>';

    $ttl = $xml->metadata->headline;
	
    if (isset($r))
        $ttl = $xml->sections->section[$r]->title;
    if (isset($t))
        $ttl .= " - " . $xml->sections->section[$r]->chapters->chapter[$t]->title;
		
    $str2 = str_replace("TITLE_TOKEN", $ttl, $str);
    return $str2;
}

function FooterStr($xml)
{
    $str = '<div class="clear"></div>' . "\n\n".
           "</div>\n\n" .
           '<div id="footer">' . "\n" .
           $xml->metadata->copyrights . ' &copy; ' . $xml->metadata->lastupdate->year .
		   ' <span class="separator">|</span> ' . 
		   _("All rights reserved") .  
           "</div>\n\n</div>\n\n</body>\n</html>\n\n";

    return $str;
}

?>
