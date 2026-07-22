<?php
/**
 * config/greetings.php - default phrase => response mapping for
 * Services\GreetingsService. Kept out of config/defaults.php since this
 * list is its own concern (small-talk data, not plugin behaviour settings)
 * and is likely to grow/change independently of everything else there.
 *
 * @return array<string, string>
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return array(
	'hello'            => 'Hello! How can I help?',
	'hi'               => 'Hi there! What are you looking for?',
	'hey'              => 'Hey! What can I help you find?',
	'good morning'     => 'Good morning! How can I help?',
	'good evening'     => 'Good evening! How can I help?',
	'good night'       => 'Good night!',
	'how are you'      => "I'm doing great, thanks for asking! How can I help you today?",
	'bye'              => 'Bye! Feel free to come back anytime.',
	'goodbye'          => 'Goodbye! Feel free to come back anytime.',
	'see you'          => 'See you soon!',
	'take care'        => 'Take care!',
	'nice to meet you' => 'Nice to meet you too!',
	'thank you'        => "You're welcome!",
	'thanks'           => "You're welcome!",
	"you're welcome"   => '😊',
);
