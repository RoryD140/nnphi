<?php

use IndefiniteArticle\IndefiniteArticle;

class IndefiniteArticleTest extends PHPUnit_Framework_TestCase {

  /**
   * Numbers
   */
  public function testNumbers() {
    $assertions = array(0 => 'a', 1 => 'a', 8 => 'an', 88 => 'an', 800 => 'an', 11 => 'an', 100 => 'a', 18 => 'an', 28 => 'a', 110 => 'a', 111 => 'a', 1111 => 'a', 1100 => 'a', 18000 => 'an', 11000000 => 'an', '-1' => 'a', '-18' => 'a');

    $this->_testAssertions($assertions);
  }

  /**
   * Ordinal forms
   */
  public function testOrdinals() {
    $assertions = array('eleventh' => 'an', 'eighth' => 'an', 'ninth' => 'a', 'one hundredth' => 'a', 'eighteenth' => 'an', 'millionth' => 'a', 'elevenhundredth' => 'an', 'hundredth' => 'a');

    $this->_testAssertions($assertions);
  }

  /**
   * Special cases
   */
  public function testSpecialCases() {
    $assertions = array('euler number' => 'an', 's' => 'an', 'x' => 'an', 'hour' => 'an', 'heir' => 'an', 'honest' => 'an', 'honorary' => 'an', 'b' => 'a', 'z' => 'a' );

    $this->_testAssertions($assertions);
  }

  /**
   * Abbreviations
   */
  public function testAbbreviations() {
    $assertions = array('mr.' => 'a', 'mrs.' => 'a', 'dr.' => 'a', 'st.' => 'a', 'x-ray' => 'an');

    $this->_testAssertions($assertions);
  }

  /**
   * Consonants
   */
  public function testConsonants() {
    $assertions = array('dog' => 'a', 'horse' => 'a', 'zealot' => 'a', 'doctor' => 'a', 'dancer' => 'a', 'car' => 'a', 'laser' => 'a', 'beer' => 'a');

    $this->_testAssertions($assertions);
  }

  /**
   * Special vowel forms
   */
  public function testSpecialVowelForms() {
    $assertions = array('european' => 'a', 'once' => 'a', 'one' => 'a', 'university' => 'a', 'universal' => 'a', 'utter' => 'an', 'useful' => 'a');

    $this->_testAssertions($assertions);
  }

  /**
   * Special capitals
   */
  public function testSpecialCapitals() {
    $assertions = array('Ukranian' => 'an', 'Uzbekistani' => 'an', 'UNO' => 'a', 'American' => 'an');

    $this->_testAssertions($assertions);
  }

  /**
   * Vowels
   */
  public function testVowels() {
    $assertions = array('apple' => 'an', 'elephant' => 'an', 'ugly duck' => 'an', 'unit' => 'a', 'itchy sweater' => 'an', 'engineer' => 'an', 'orange' => 'an');

    $this->_testAssertions($assertions);
  }

  /**
   * Y
   */
  public function testY() {
    $assertions = array('year' => 'a', 'yellow' => 'a', 'yclad' => 'an');

    $this->_testAssertions($assertions);
  }

  /**
   * Actually test assertions
   *
   * @param  array  $assertions Array of key/value assertions
   */
  private function _testAssertions($cases = array()) {
    foreach ( $cases as $case => $prefix ) {
      // ensure that the expected prefix is added to the original case
      $a = IndefiniteArticle::A($case);
      $this->assertEquals($prefix . ' ' . $case, $a);

      // make sure A() and An() return the same response
      $an = IndefiniteArticle::An($case);
      $this->assertEquals($a, $an);
    }
  }
}
