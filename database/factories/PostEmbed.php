<?php 
	$faker = new Faker\Generator();

	$factory->define(App\Embed::class, function(Faker\Generator $faker) {
		$post = App\Post::find(rand(50000, 50390));
		$user = App\User::find(rand(1, 22017));

		// Import Internet Provider
		$faker->addProvider(new Faker\Provider\Internet($faker));

		return [
			'id_post'    => (empty($post) ? 50390 : $post->id),
			'accessurl'  => $faker->url(),
			'domain'     => $faker->domainName(),
			'shareid'    => (empty($user) ? 22017 : $user->id),
			'view'       => rand(1000, 200000),
			'created_on' => date('Y-m-d H:i:s') 
		];
	});

?>