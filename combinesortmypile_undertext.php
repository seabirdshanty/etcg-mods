<?php 
/* 
==========================================
COMBINESORTMYPILE_UNDERTEXT.PHP
BY: RIZU, JOEY, MUFFY
==========================================

Can you tell I really like organizing my pile? Or Using the undertext mod? I'm going insane yall :^)
I could prolly just put everything in one category but non, That would not be organized enough for me LMAO

--------------------
FUNCTION OVERVIEW
--------------------
combinesortmypile($tcg, $doubles = 1, $startSort = '0', $endSort = 'z', $text = false, $cata, $catb, $catc = '', $catd = '', $cate = '')

  Combine up to 5 categories into one pile, then OPTIONALLY sort them depending on your query.
  This functionally replaces Rizus combinemypile, so you can remove and replace that if you like.
  
  - string $tcg = the name of the TCG as defined in the database.
  - int $doubles = 0 show all cards; 1 shows only unique cards; 2 shows only doubles.
  - string $startSort = The value you want sort to start with; 
  - string $endSort = The value you want sort to end with;
  - bool $text = display pile as text (default: false)
  - strings $cata, $catb, $catc, $catd, $cate = the categories you want to combine. a and b are obligatory. 

--------------------
EXPLAIN TO ME IN ENGLISH!
--------------------

This wondapri function does 4 things:

1. Combines 2 or more categories UP TO 5 categories (Rizu @ http://tcg.haltfate.org/stuff.php)
    NOTE: You MUST combine at least 2 piles, 3-5 are optional.
2. SORTS these categories (Rizu), Now optional 
    (Will sort 0 - Z by Default)
3. Show All Cards, Unique Cards, or Doubles , Now optional! 
    (Will Show ALL Cards By Default)
3. can print as either IMAGES with UNDERTEXT (Joey @ https://tcg.hopeful-despair.net/showtext.php) or PLAINTEXT (Myself) 
    (Will Show Images with Undertext by Default)

NOTE: This does NOT include the Pending in Keeping Mod. I'll get to it when I can.
This can be used with Caitlins Show Cards as Text search mod @ https://idolisetutorials.notion.site/Show-cards-as-text-e32c64773294482e9c462994ae5db729 !

Majority of Code Belongs to Rizu & Joey. PLEASE credit them when using this code.
I also did my best to OPTIMIZE Rizus sort code for doubles cause uh. That repeated 3 times and I went nuts copy-pasting it all. rip

--------------------
EXAMPLES
--------------------

EXAMPLE 1: I want to combine my 2 trading piles, then sort them from 0 - m:
  combinesortmypile('tcghere', '',  0, 'm', '', 'trading', 'trading_new');

EXAMPLE 2: I want to combine my 5 keeps piles, showing only unique cards, organize a - j, and print as text
  combinesortmypile('tcghere', 1,  'a', 'j', true, 'keeps_high', 'keeps_med', 'keeps_low', 'keeps_spec', 'keeps_puz');

EXAMPLE 3: I want to show ONLY my doubles from my 3 futures piles, No sorting
  combinesortmypile ('tcghere', 2, '', '', '', 'futures_boxset', 'futures_aria', 'futures_jun');


PLACE AT THE BOTTOM OF YOUR MODS.PHP FILE
YOU CAN REMOVE THE ABOVE README FOR MINIMIZING YOUR FILE.

*/
function combinesortmypile($tcg, $doubles = 0, $startSort = '0', $endSort = 'z', $text = false, $cata, $catb, $catc = '', $catd = '', $cate = '') {
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);
	
	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $tcginfo['id'];
	$cardsurl = $tcginfo['cardsurl'];
	$format = $tcginfo['format'];
	
	// first, grab cards from all categories
	$cata = $sanitize->for_db($cata); $catb = $sanitize->for_db($catb); $catc = $sanitize->for_db($catc); $catd = $sanitize->for_db($catd); $cate = $sanitize->for_db($cate);
	$cardsa = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$cata' LIMIT 1");
	$cardsb = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$catb' LIMIT 1");

	// optional categories
	if($catc === '') { $cardsc = ''; } else { $cardsc = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$catc' LIMIT 1"); $cardsc = $cardsc['cards']; }
	if($catd === '') { $cardsd = ''; } else { $cardsd = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$catd' LIMIT 1"); $cardsd = $cardsd['cards']; }
	if($cate === '') { $cardse = ''; } else { $cardse = $database->get_assoc("SELECT `cards`, `format` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$cate' LIMIT 1"); $cardse = $cardse['cards']; }

	// push all cards to one single string
	$cards = $cardsa['cards'] . ', ' . $cardsb['cards'] . ', ' . $cardsc . ', ' . $cardsd . ', ' . $cardse;
	
	// get the sort ready
	$startSort = $sanitize->for_db($startSort);
	$startSort = strtoupper($startSort);
	$endSort = $sanitize->for_db($endSort);
	$endSort = strtoupper($endSort);
	$searchme = '/['.$startSort.'-'.$endSort.']/i';

	if ( $cards === ', , , ,' ) { 
		echo '<p class="cards"><em>There are currently no cards under this category.</em></p>'; 
	} 
	else {
		
		$cardsall = explode(', ',$cards); // explode the cards string
		sort($cardsall); // sort cards
		$array_count = count($cards);	// count the number of cards?? idk why this is here, it was in Rizu's OG code. Maybe for Combined Worth?

		$cardsall = array_map(trim, $cardsall);	// all cards		
		$cardsuni = array_unique($cardsall); // all unique/first cards
		$cardsdou = array_diff_assoc($cardsall, array_unique($cardsall)); // all doubles

		// check what cards were using
		$cardsInPlay = array();	
		if( $doubles == 0 ) { $cardsInPlay = $cardsall;} 
    		elseif( $doubles == 1) { $cardsInPlay = $cardsuni; }
    		elseif( $doubles == 2) {  $cardsInPlay = $cardsdou; }

		echo "<ul class=\"list-inline\">";
  		foreach ( $cardsInPlay as $card ) { 
  			$card = trim($card); 
  			if($card != '' && preg_match("$searchme", $card[0])){ // you NEED the bracket integer [0] so the sort works!
  				if ( $text == true ) { echo '<span title="' .$card. '">'.$card.'</span>, '; }
  				else { echo '<li><img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$card.'" /><span class="cardname">'.$card.'</span></li>';}
  			}
  		}
  		echo "</ul>";
  		unset($card);
	}
}

?>

<!-- Add this to your Stylesheet -->
<style>
#cardlist li {
    display: inline-block;
}
.cardname { line-height: 25px;
    width: 115px;
    display: block;
    font-size: 11px;
    margin: 4px;
  }
</style>
