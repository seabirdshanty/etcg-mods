<?php define('VALID_INC', TRUE); include_once 'func.php';
/* 
==============================================
    Card Sort via category
    by Muffy
==============================================

Sorts cards for you.
Place cards into the "sortme" category, then refresh the page for all of these cards to be sorted for you :3
One day this will allow the log input that the idolise cardsort does.
(the coding of that confused me, so i coded my own)

-------------
HOW TO USE:
-------------
1. Place this file in the same folder as your TCG.
2. Create a category in eTCG called "sortme" (or whatever you prefer, you can change this in config below) 
  - with a cardworth of 0 
  - no autoupload
3. While you're proccessing your logs, put cards you want to sort into this category.
4. Refresh this page, and the cards will be sorted into categories for you.
5. Once your sorting is complete, pull all your cards out of the sortme category.
6. Rinse and Repeat!

This is easier to me, that way i process my cards while logging them all
Unfortunately this doesn't allow for external input, or checks for EXACT cards, just what decks are where.

-------------
Func Calls
-------------
NOTE: This is it's own standalone page, reading database info from your func.php. 
Make sure none of the function calls interact with any of your mods.

grabCats()
catCards()
deckArray()
cardArray()
replaceNumber()

==============================================
CONFIG
============================================== */

// name of the TCG you're working with
$workTCG = "tcgnamehere";

// name of the Sorting category, default "sortme"
$mySortCat = "sortme";

/* ==============================================
        Do not touch anything below 
        lest you know what youre doing.
 ============================================== */

$workArray = array();
$inputArrays = array();

function grabCats( $tcg ) {
  // grabs categories from the database
	$holdCats = array(); 
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);

	$result = $database->get_assoc("SELECT `id` FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $result['id'];

	$counter = 1;

	while ($counter > 0) {
		$result = $database->get_assoc("SELECT * FROM `cards` WHERE `tcg`='$tcgid' AND `id`='$counter' ");
		$pushMe = $result['category'];
		if($pushMe == "") { 
			// check next value
			$checkIt = $counter + 1;
		    $wonder = $database->get_assoc("SELECT * FROM `cards` WHERE `tcg`='$tcgid' AND `id`='$checkIt' ");
			$wonder = $wonder['category'];
			if($wonder == "") {
				unset( $wonder, $pushMe, $result );
				break; 
			}
		} else {
			array_push($holdCats, $pushMe);
		}
		$counter++;
	}
	return $holdCats;
}

function catCards( $tcg, $category, $worth = '') {
	$database = new Database;
	$sanitize = new Sanitize;
	$tcg = $sanitize->for_db($tcg);
	$category = $sanitize->for_db($category);

	$result = $database->get_assoc("SELECT `id` FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
	$tcgid = $result['id'];
  
  $result = $database->get_assoc("SELECT `cards` FROM `cards` WHERE `tcg`='$tcgid' AND `category`='$category' LIMIT 1");
	return $result['cards'];
}

function deckArray ( string $inputCards ) {
	$exportArray = array();
  
	// explode each card
	$inputArray = explode(', ', $inputCards );
  
	// remove duplicates
	$inputArray = array_values(array_unique($inputArray));

  //push to return array
	foreach ( $inputArray as &$value ) {
		array_push($exportArray, $value);
	}

  // unset foreach
	unset( $value );
  
	// return!
	return $exportArray;
}

function cardArray ( string $inputCards ) {
	$exportArrayTwo = array();
  
	// explode each card
	$exportArrayTwo = explode(', ', $inputCards );
  
  // return
	return $exportArrayTwo;
}


function replaceNumber( string $fixMe ) {
  // replaces numbers in a string uwu
	$result = "";
	$result = preg_replace('/[0-9]+/', '', $fixMe);
	return $result;
}


// find my categories
$myCategories = grabCats( $workTCG ); // grab your categories
$countMeIn = sizeof($myCategories);

// grab your inputs
$inputCards =   catCards( $workTCG, $mySortCat ); 
$sayArray = cardArray($inputCards);

// grab your input decks
$inputCardsTwo = replaceNumber($inputCards);
$sayArrayCheck = deckArray($inputCardsTwo);

echo "<h2>Card Sort from the sortme Category</h2>";

$excludeMe = array();

foreach( $myCategories as &$meow ) {
	if ( $meow !== $mySortCat ) {
		// grab cards
		$workingCards = catCards( $workTCG, $meow );
		if($workingCards !== "") {
      
			// remove numbers
			$workingCards = replaceNumber($workingCards);
      
			// create working array
			$workArray = deckArray($workingCards);

      // find matches in the array
			$foundArray = array_intersect($workArray, $sayArrayCheck);

      // check if there were any matches
			if(count($foundArray) > 0) {
				echo "<p><h3>" . $meow . "</h3>";
				foreach ( $foundArray as $decksToFind) {
          // dig through the found array
					foreach( $sayArray as $cardsToFind) {
            // dig through the input array
						if( strpos($cardsToFind, $decksToFind ) !== false && $decksToFind !== "") {
              // if you find cards, print them!
							echo $cardsToFind . ", ";
              // push cards we've already found to another array
							array_push ($excludeMe, $cardsToFind);
						}
					}
				}
				echo "</p>";
			}
		}
	}
}

// unset foreach variables
unset($meow, $cardsToFind, $decksToFind);

// check if theres anymore cards to print
$excludeMeSize = sizeof($excludeMe);
$sayArraySize = sizeof($sayArray);

if( $sayArraySize !== $excludeMeSize ) {
	echo "<h3>Unsorted Cards</h3>";
  
  // find the extra cards
	$extraCards = array_diff($sayArray, $excludeMe);
  // make the array a string
	$extraCardsPrint = implode(", ", $extraCards);
  // print the extra cards!
	echo $extraCardsPrint;
}

?>
