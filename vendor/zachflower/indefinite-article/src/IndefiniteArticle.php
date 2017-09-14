<?php namespace IndefiniteArticle;

class IndefiniteArticle {

  /**
   * Determine appropriate indefinite article
   *
   * @param  string $input A word or phrase
   * @return string        Original word or phrase prefixed with 'a' or 'an'
   */
  public static function A($input) {
    $matches = array();

    preg_match("/\A(\s*)(?:an?\s+)?(.+?)(\s*)\Z/i", $input, $matches);

    list($all, $pre, $word, $post) = $matches;

    if ( is_null($word) ) {
      return $input;
    }

    $result = self::_indef_article($word);

    return $pre.$result.$post;
  }

  /**
   * Alias of the A() static method
   */
  public static function An($input) {
    return self::A($input);
  }

  /**
   * Determine appropriate indefinite article for a given word
   *
   * @param  string $word A word
   * @return string       Original word prefixed with 'a' or 'an'
   */
  private static function _indef_article($word) {
    // any number starting with an '8' uses 'an'
    if ( preg_match("/^[8](\d+)?/", $word)) {
      return "an $word";
    }

    // numbers starting with a '1' are trickier, only use 'an' if there are
    // 3, 6, 9, … digits after the 11 or 18
    if ( preg_match("/^[1][1](\d+)?/", $word) || (preg_match("/^[1][8](\d+)?/", $word))) {
      if ( strlen(preg_replace(array("/\s/", "/,/", "/\.(\d+)?/"), '', $word)) % 3 == 2) {
        return "an $word";
      }
    }

    // ordinal forms
    if ( preg_match("/^([bcdgjkpqtuvwyz]-?th)/i", $word)) {
      return "a $word";
    }

    if ( preg_match("/^([aefhilmnorsx]-?th)/i", $word)) {
      return "an $word";
    }

    // special cases
    if ( preg_match("/^(euler|hour(?!i)|heir|honest|hono)/i", $word)) {
      return "an $word";
    }

    if ( preg_match("/^[aefhilmnorsx]$/i", $word)) {
      return "an $word";
    }

    if ( preg_match("/^[bcdgjkpqtuvwyz]$/i", $word)) {
      return "a $word";
    }

    // abbreviations
    if ( preg_match("/^((?! FJO | [HLMNS]Y.  | RY[EO] | SQU | ( F[LR]? | [HL] | MN? | N | RH? | S[CHKLMNPTVW]? | X(YL)?) [AEIOU]) [FHLMNRSX][A-Z])/x", $word)) {
      return "an $word";
    }

    if ( preg_match("/^[aefhilmnorsx][.-]/i", $word)) {
      return "an $word";
    }

    if ( preg_match("/^[a-z][.-]/i", $word)) {
      return "a $word";
    }

    // consonants
    if ( preg_match("/^[^aeiouy]/i", $word)) {
      return "a $word";
    }

    // special vowel forms
    if ( preg_match("/^e[uw]/i", $word)) {
      return "a $word";
    }

    if ( preg_match("/^onc?e\b/i", $word)) {
      return "a $word";
    }

    if ( preg_match("/^uni([^nmd]|mo)/i", $word)) {
      return "a $word";
    }

    if ( preg_match("/^ut[th]/i", $word)) {
      return "an $word";
    }

    if ( preg_match("/^u[bcfhjkqrst][aeiou]/i", $word)) {
      return "a $word";
    }

    // special capitals
    if ( preg_match("/^U[NK][AIEO]?/", $word)) {
      return "a $word";
    }

    // vowels
    if ( preg_match("/^[aeiou]/i", $word)) {
      return "an $word";
    }

    // before certain consonants, y implies an "i" sound
    if ( preg_match("/^(y(b[lor]|cl[ea]|fere|gg|p[ios]|rou|tt))/i", $word)) {
      return "an $word";
    }

    // not sure, so guess 'a'
    return "a $word";
  }
}
