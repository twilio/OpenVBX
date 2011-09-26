<?php

// Include Tropo classes.
require('tropo.class.php');

// Include Limonade framework.
require('path/to/limonade/lib/limonade.php');

// The URL to the Google weather service. Renders as XML doc.
define("GOOGLE_WEATHER_URL", "http://www.google.com/ig/api?weather=%zip%&hl=en");

// A helper method to get weather details by zip code.
function getWeather($zip) {
	
	$url = str_replace("%zip", $zip, GOOGLE_WEATHER_URL);
	$weatherXML = simplexml_load_file($url);
	$city = $weatherXML->weather->forecast_information->city["data"];
	$current_conditions = $weatherXML->weather->current_conditions;
	$current_weather = array(
				"condition" => $current_conditions->condition["data"], 
				"temperature" => $current_conditions->temp_f["data"]." degrees", 
				"wind" => formatDirection($current_conditions->wind_condition["data"]),
				"city" => $city
	);
	return $current_weather;
	
}

// A helper method to format directional abbreviations.
function formatDirection($wind) {
	$abbreviated = array(" N ", " S ", " E ", " W ", " NE ", " SE ", " SW ", " NW ");
	$full_name = array(" North ", " South ", " East ", " West ", " North East ", " South East ", " South West ", " North West ");
	return str_replace($abbreviated, $full_name, str_replace("mph", "miles per hour", $wind));
}

// A helper method to format the Tropo object with weather details for a specific zip code.
function formatWeatherResponse(&$tropo, $zip) {
	
	// Get weather information for the zip code the caller entered.	
	$weather_info = getWeather($zip);
	$city = array_pop($weather_info);

    // Begin telling the user the weather for the city their zip code is in. 
	$tropo->say("The current weather for $city is...");

    // Iterate over an array of weather information.
	foreach ($weather_info as $info) {
		$tropo->say("$info.");
	}
	
    // Say thank you (never hurts to be polite) and end the session.
	$tropo->say("Thank you for using Tropo!");
    $tropo->hangup();
}

/**
 * This is the starting point for the Tropo application.
 * Get a 5 digit zip code from the user.
 */
dispatch_post('/start', 'zip_start');
function zip_start() {
	
	// Create a new instance of the Session object, and get the channel information.
	$session = new Session();
	$from_info = $session->getFrom();
	$channel = $from_info['channel'];
	
	// Create a new instance of the Tropo object.
	$tropo = new Tropo();
	
	// See if any text was sent with session start.
	$initial_text = $session->getInitialText();
	
	// If the initial text is a zip code, skip the input collection and get weather information.
	if(strlen($initial_text) == 5 && is_numeric($initial_text)) {
		formatWeatherResponse($tropo, $initial_text);
	}
	
	else {
		// Welcome prompt (for phone channel, and IM/SMS sessions with invalid initial text).
		$tropo->say("Welcome to the Tropo PHP zip code example for $channel");
		
		// Set up options form zip code input
		$options = array("attempts" => 3, "bargein" => true, "choices" => "[5 DIGITS]", "name" => "zip", "timeout" => 5);
		
		// Ask the user for input, pass in options.
		$tropo->ask("Please enter your 5 digit zip code.", $options);
		
		// Tell Tropo what to do when the user has entered input, or if there is an error.
		$tropo->on(array("event" => "continue", "next" => "get_zip_code.php?uri=end", "say" => "Please hold."));
		$tropo->on(array("event" => "error", "next" => "get_zip_code.php?uri=error", "say" => "An error has occured."));
	}
	
	// Render the JSON for the Tropo WebAPI to consume.
	return $tropo->RenderJson();
	
}

/**
 * After a zip code has been entered, use it to look up weather details for that city.
 */
dispatch_post('/end', 'zip_end');
function zip_end() {
	
    // Create a new instance of the result object and get the value of the user input.
	$result = new Result();
	$zip = $result->getValue();
	
	// Create a new instance of the Tropo object.
	$tropo = new Tropo();
	
    // Get the weather information for the entered zip code.
	formatWeatherResponse($tropo, $zip);
	
    // Render the JSON for the Tropo WebAPI to consume.
    return $tropo->RenderJson();
	
}

/**
 * If an error occurs, end the session.
 */
dispatch_post('/error', 'zip_error');
function zip_error() {
	
	// Step 1. Create a new instance of the Tropo object.
	$tropo = new Tropo();
	
	// Step 2. This is the last thing the user will be told before the session ends.
	$tropo->say("Please try your request again later.");
	
	// Step 3. End the session.
	$tropo->hangup();
	
	// Step 4. Render the JSON for the Tropo WebAPI to consume.
	return $tropo->renderJSON();
}

// Run this sucker!
run();

?>