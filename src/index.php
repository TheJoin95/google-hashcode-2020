<?php

/**
* Books: id form 0 to B-1, 1:M Library
* we need to scan a book only ONCE
* book props: id(int) and score(float)
*/

/*
* Libraries: id from 0 to L-1
* lib props: 
	- set of books available in lib
	- time, in days, that takes to sign the library for scan
	- n books that can be scanned each day, after signup days
*/

/**
* Time
* Days: day index from 0 to D-1
* The first day is Day 0
* D-1 day is the last day which books can be shipped for scan
*/

/**
* Lib signup
* One lib at time
* One signup process at time
* Library can signup in any order
*/

/* BOOK CAN BE SCANNED IN PARALLEL FROM MULTIPLE LIBS */

/* SCANNING 
* The limit is lib max books per day
*/

/*
input file: 3 rows after the header
0: books, libraries, days (header)
1: scores of the book (index position)
	2: block of 3: (0 => books, 1=> signup process days, 2=>max_books_day)
	3: books in library 0
*/

/*
output file:
0 => integer of how many libs I'm scanning (HEADER)
	1 => (library id, n books) to send after signup
	2 => book that lib of 1 will send
*/
ini_set('auto_detect_line_endings',true);
const FILES = [
	"a" => "a_example.txt",
	"d" => "d_tough_choices.txt",
	"b" => "b_read_on.txt",
	"e" => "e_so_many_books.txt",
	"c" => "c_incunabula.txt",
	"f" => "f_libraries_of_the_world.txt"
];

$submissionLetter = "e";

foreach (FILES as $submissionLetter => $nope) {

$fpInput = fopen('./case/' . FILES[$submissionLetter], 'r');
$fpOutput = fopen('./submissions/'.$submissionLetter.'.txt', 'w+');

list($nTotBooks, $nLibraries, $nDaysScanning) = explode(' ', fgets($fpInput));
printf('b: %s, l: %s, d: %s', $nTotBooks, $nLibraries, $nDaysScanning);

$bookScores = explode(' ', fgets($fpInput));

$libraries = $booksPerLib = $scannedBooks = [];
$rowIteratorCounter = 0;

while(($row = fgets($fpInput))){
	if($row == "\n") continue;
	if ($rowIteratorCounter % 2 != 0) {
		$booksPerLib[] = explode(' ', str_replace("\n", "", $row));
		$lastIndex = count($booksPerLib)-1;
		$libraries[$lastIndex]['max_book_score'] = max($booksPerLib[$lastIndex]);
		$libraries[$lastIndex]['avg_book_score'] = floor(array_sum($booksPerLib[$lastIndex]) / count($booksPerLib[$lastIndex]));
	} else {
		list($nLibBooks, $signupDays, $booksPerDay) = explode(' ', $row);
		$libraries[] = [
			"n_books" => (int)$nLibBooks,
			"signup_days" => (int)$signupDays,
			"books_per_day" => (int)$booksPerDay
		];
	}
	$rowIteratorCounter++;
}

$ord = 'minDaysFirst';
if($submissionLetter == 'd') 
	$ord = 'avgBooksScore';// 'maxBookScore';

uasort($libraries, $ord);

/*if($submissionLetter == 'd' || $submissionLetter == 'e')
	uasort($libraries, 'maxBooksPerDay');*/

$rowOutput = [];
$totLib = count($libraries);
foreach($libraries as $libIndex => $library) {
	$numBooks = floor($library["books_per_day"] * (int)$nDaysScanning);

	if ($numBooks > $nTotBooks) {
		$numBooks = $nTotBooks;
	}

	if ($numBooks > $library["n_books"]) {
		$numBooks = $library["n_books"];
	}

	$lineOutput = array();
	$realNBook = 0;
	for($i = 0; $i < $numBooks; $i++) {
		if(!isset($booksPerLib[$libIndex][$i])) break;
		if(isset($scannedBooks[$booksPerLib[$libIndex][$i]])) continue;

		$scannedBooks[$booksPerLib[$libIndex][$i]] = true;
		$lineOutput[] = $booksPerLib[$libIndex][$i];
		$realNBook++;
	}

	if(empty($lineOutput)) {
		$totLib--;
		continue;
	}

	$numBooks = $realNBook;
	$lineNBooksOutput = array($libIndex, $numBooks);
	$rowOutput[] = $lineNBooksOutput;
	$rowOutput[] = $lineOutput;

}

fwrite($fpOutput, $totLib."\n");
foreach ($rowOutput as $row) {
	fwrite($fpOutput, implode(' ', $row)."\n");
}

fclose($fpInput);
fclose($fpOutput);
}

function avgBooksScore($a, $b) {
    if ($a['avg_book_score'] == $b['avg_book_score']) {
        return ($a['max_book_score'] > $b['max_book_score']) ? -1 : 1;
    }
    return ($a['avg_book_score'] > $b['avg_book_score']) ? -1 : 1;
}

function maxBooksPerDay($a, $b) {
    if ($a['books_per_day'] == $b['books_per_day']) {
        return ($a['max_book_score'] > $b['max_book_score']) ? -1 : 1;
    }
    return ($a['books_per_day'] > $b['books_per_day']) ? -1 : 1;
}

function maxBookScore($a, $b) {
    if ($a['max_book_score'] == $b['max_book_score']) {
        return 0;
    }
    return ($a['max_book_score'] > $b['max_book_score']) ? -1 : 1;
}

function minDaysFirst($a, $b) {
    if ($a['signup_days'] == $b['signup_days']) {
        return ($a['max_book_score'] > $b['max_book_score']) ? -1 : 1;
    }
    return ($a['signup_days'] < $b['signup_days']) ? -1 : 1;
}
?>