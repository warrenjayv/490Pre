<?php

  $text = "def fuckmeintheassharder(a, b) return \"fucking bitch\""; 
  function wrongfunc($text) {
      /* returns the wrong function */ 
      $prant='('; 
      $length = strlen($text);
      $start = stripos($text, "def", 0); $start+=4; 
      $end = stripos($text, $prant, 0); 
      $wrong = substr($text, $start, -($length - $end)); 
      return $wrong; 
  }

  echo wrongfunc($text); 
?>
