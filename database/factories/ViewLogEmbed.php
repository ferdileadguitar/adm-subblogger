<?php 

	$factory->define(App\EmbedLog::class, function(Faker\Generator $faker) {

		// Import Internet Provider
		$faker->addProvider(new Faker\Provider\Internet($faker));
		
		return [
			'ip_address' 		=> $faker->ipV4(),
			'user_agent'		=> $faker->userAgent(),
			'user_id'			=> 2,
			'post_id' 			=> App\Post::where('user_id', 2)->first()->id,
			'domain'			=> $faker->domainName,
			'shareid' 			=> 22015,
			'accessurl'			=> $faker->url(),
			'cookie_id' 		=> '',
			'token_id' 			=> 'eVOHYGLziDA6ddCf9MVzZqdzTnJC67tQFk1WkYQ4',
			'last_activity' 	=> $faker->unixTime($min = '-30 days', $max = 'now')
		];
	});
?>