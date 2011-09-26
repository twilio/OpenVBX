Overview
========

TropoPHP is a set of PHP classes for working with [Tropo's cloud communication service](http://tropo.com/). Tropo allows a developer to create applications that run over the phone, IM, SMS, and Twitter using web technologies. This library communicates with Tropo over JSON.

Requirements
============

 * PHP 5.3.0 or greater
 * PHP Notices disabled (All error reporting disabled is recommended for production use)

Usage
=====

Answer the phone, say something, and hang up.

    <?php    
    require 'tropo.class.php';

    $tropo = new Tropo();    
    // Use Tropo's text to speech to say a phrase.    
    $tropo->say('Yes, Tropo is this easy.');    

    // Render the JSON back to Tropo.
    $tropo->renderJSON();    
    ?>    
    
Asking for input.

    <?php
    require 'tropo.class.php';

    $tropo = new Tropo();
    $tropo->ask('What is your favorite programming language?', array(
      'choices'=>'PHP, Ruby(Ruby, Rails, Ruby on Rails), Python, Java(Groovy, Java), Perl',
      'event'=> array(
        'nomatch' => 'Never heard of it.',
        'timeout' => 'Speak up!',
        )
      ));
    // Tell Tropo how to continue if a successful choice was made
    $tropo->on(array('event' => 'continue', 'say'=> 'Fantastic! I love that, too!'));
    // Render the JSON back to Tropo    
    $tropo->renderJSON();
    ?>