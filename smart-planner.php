<?php
// Smart Planner Page - Voyago Malaysia
require_once('toyyibpay_config.php');

// 1. DATABASE SCHEMA AUTO-CREATION & TRANSACTION-OPTIMIZED SEEDER
try {
    // Create the attractions table if it does not exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS `attractions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `state` varchar(50) NOT NULL,
        `category` varchar(50) NOT NULL,
        `name` varchar(150) NOT NULL,
        `description` text NOT NULL,
        `recommended_time` varchar(50) NOT NULL,
        `maps_link` text DEFAULT NULL,
        `image_url` varchar(255) DEFAULT NULL,
        `rating` decimal(3,1) DEFAULT 4.5,
        `reviews_count` varchar(50) DEFAULT '100 reviews',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    // Check if the attractions table is empty
    $count = $pdo->query("SELECT COUNT(*) FROM `attractions`")->fetchColumn();
    if ($count == 0) {
        $initial_gems = [
            // --- TERENGGANU ---
            ['Terengganu', 'Beach', 'Pulau Gemia', 'Private island paradise offering unparalleled seclusion and exquisite marine ecosystems.', '4-6 Hours', 'https://maps.google.com', 'gemia.png', 4.8, '1,240 reviews'],
            ['Terengganu', 'Beach', 'Pantai Kelulut', 'Serene stretch of sandy beach flanked by local traditional seafood pavilions.', '1-2 Hours', 'https://maps.google.com', 'kelulut.jpg', 4.5, '540 reviews'],
            ['Terengganu', 'Beach', 'Pantai Kemasik', 'Famous for its unique structural rock formations rising majestically out of the lagoon.', '2-3 Hours', 'https://maps.google.com', 'kemasik.jpg', 4.6, '720 reviews'],
            ['Terengganu', 'Beach', 'Pulau Kapas', 'Beautiful hidden island with crystal clear emerald waters and fewer tourist crowds.', '5-8 Hours', 'https://maps.google.com', 'kapas.jpg', 4.8, '1,240 reviews'],
            ['Terengganu', 'Nature', 'Sekayu Waterfall', 'Multi-tiered cascades set inside a lush, tranquil forest reserve with natural pools.', '3-4 Hours', 'https://maps.google.com', 'sekayu.jpg', 4.5, '820 reviews'],
            ['Terengganu', 'Nature', 'Bukit Keluang', 'Offers dynamic, panoramic coastal vistas where the jungle meets the sea cliff edges.', '2-3 Hours', 'https://maps.google.com', 'keluang.jpg', 4.7, '1,100 reviews'],
            ['Terengganu', 'Nature', 'Tasik Kenyir', 'Southeast Asia\'s largest artificial lake, rich with secret coves and floating sanctuaries.', '1-2 Days', 'https://maps.google.com', 'kenyir.jpg', 4.6, '980 reviews'],
            ['Terengganu', 'Nature', 'Lata Belatan', 'Enchanting forest eco-park serving as the main gateway to Mount Tebu.', '2-4 Hours', 'https://maps.google.com', 'belatan.jpg', 4.4, '310 reviews'],
            ['Terengganu', 'Adventure', 'ATV Pantai Penarik', 'Thrilling all-terrain vehicle ride cruising right across the long shoreline.', '1-2 Hours', 'https://maps.google.com', 'penarik_atv.jpg', 4.6, '240 reviews'],
            ['Terengganu', 'Adventure', 'Kayaking Kenyir', 'Paddle through historic submerged gorges and pristine ancient rainforest canopies.', '3-5 Hours', 'https://maps.google.com', 'kenyir_kayak.jpg', 4.7, '180 reviews'],
            ['Terengganu', 'Adventure', 'Island Hopping Marang', 'High-speed boat venture to isolated sandbars and secret marine sanctuaries.', '4-6 Hours', 'https://maps.google.com', 'marang.jpg', 4.8, '670 reviews'],
            ['Terengganu', 'Adventure', 'Hiking Bukit Besi', 'Challenging jungle trekking exploring historical remains of regional iron mining.', '3-4 Hours', 'https://maps.google.com', 'bukitbesi.jpg', 4.3, '120 reviews'],
            ['Terengganu', 'Culture', 'Chinatown KT', 'Vibrant historic enclave with heritage shophouses, street murals, and old clan houses.', '2-3 Hours', 'https://maps.google.com', 'chinatown_kt.jpg', 4.5, '850 reviews'],
            ['Terengganu', 'Culture', 'Pasar Payang', 'Centuries-old market center rich with authentic local batik, crafts, and traditional snacks.', '2-3 Hours', 'https://maps.google.com', 'pasarpayang.jpg', 4.4, '1,450 reviews'],
            ['Terengganu', 'Culture', 'Kampung Losong', 'The legendary heritage village home to authentic traditional fish cracker (Keropok Lekor) artisans.', '1 Hour', 'https://maps.google.com', 'losong.jpg', 4.6, '680 reviews'],
            ['Terengganu', 'Culture', 'Terrapuri Heritage Village', 'Conservation project showcasing 29 classic, majestic antique Malay palaces.', '2-3 Hours', 'https://maps.google.com', 'terrapuri.png', 4.8, '1,240 reviews'],
            ['Terengganu', 'Food', 'Nasi Dagang Atas Tol', 'Authentic local breakfast spot famous for soft, aromatic Nasi Dagang served with tuna curry.', '1-2 Hours', 'https://maps.google.com', 'nasidagang.jpg', 4.9, '2,100 reviews'],
            ['Terengganu', 'Food', 'Warung Syukur Keropok Lekor', 'Savor crispy, fresh keropok lekor fried hot on the spot along the coastal road.', '1 Hour', 'https://maps.google.com', 'keropok_lekor.jpg', 4.8, '1,320 reviews'],

            // --- PERAK ---
            ['Perak', 'Beach', 'Coral Beach Pangkor', 'Secluded pristine white sand strip perfect for quiet sunsets and clean waters.', '2-4 Hours', 'https://maps.google.com', 'coralbeach.jpg', 4.7, '890 reviews'],
            ['Perak', 'Beach', 'Teluk Batik', 'Lovely dynamic bay bordered by sweeping coconut trees and beachfront markets.', '2-3 Hours', 'https://maps.google.com', 'telukbatik.jpg', 4.3, '1,200 reviews'],
            ['Perak', 'Beach', 'Teluk Nipah', 'Energetic coastal village slice offering local watersports and stunning view.', '3-5 Hours', 'https://maps.google.com', 'teluknipah.jpg', 4.5, '1,500 reviews'],
            ['Perak', 'Beach', 'Pulau Giam', 'Tiny islet reachable by foot during low tides, hosting vibrant shallow corals.', '2-3 Hours', 'https://maps.google.com', 'pulaugiam.jpg', 4.6, '410 reviews'],
            ['Perak', 'Nature', 'Kuala Sepetang Mangrove', 'Malaysia\'s best-managed mangrove ecosystem, filled with wildlife and bird species.', '3-4 Hours', 'https://maps.google.com', 'sepetang.jpg', 4.5, '620 reviews'],
            ['Perak', 'Nature', 'Kek Lok Tong', 'Breathtaking cavern system houses detailed Buddhist altars inside massive limestone spaces.', '1-2 Hours', 'https://maps.google.com', 'kekloktong.jpg', 4.7, '1,800 reviews'],
            ['Perak', 'Nature', 'Royal Belum Rainforest', 'An ancient forest reserve older than the Amazon, housing rare hornbills and elephants.', '1-2 Days', 'https://maps.google.com', 'royalbelum.jpg', 4.8, '560 reviews'],
            ['Perak', 'Nature', 'Gua Tempurung', 'One of the longest and most spectacular natural cave networks across Peninsula Malaysia.', '3-4 Hours', 'https://maps.google.com', 'guatempurung.jpg', 4.6, '1,240 reviews'],
            ['Perak', 'Adventure', 'White Water Rafting Gopeng', 'High-octane adrenaline rush down class I-IV rapids of the scenic Kampar River.', '3-4 Hours', 'https://maps.google.com', 'gopeng_rafting.jpg', 4.8, '450 reviews'],
            ['Perak', 'Adventure', 'Hiking Bukit Larut', 'Trek up Maxwell Hill amidst cool misty climates and historical bungalow ruins.', '4-6 Hours', 'https://maps.google.com', 'bukitlarut.jpg', 4.5, '310 reviews'],
            ['Perak', 'Adventure', 'Water Tubing Kampar', 'Fun, relaxing floating downstream along picturesque river rapids wrapped in greenery.', '2 Hours', 'https://maps.google.com', 'kampar_tubing.jpg', 4.6, '220 reviews'],
            ['Perak', 'Adventure', 'Caving Adventure Gua Kandu', 'Demanding deep cave exploration mapping dark chambers and tight rock crawl spaces.', '3-5 Hours', 'https://maps.google.com', 'guakandu.jpg', 4.5, '180 reviews'],
            ['Perak', 'Culture', 'Ipoh Old Town Heritage Walk', 'Discover colonial landmarks, Concubine Lane alleyways, and old wall art gems.', '2-4 Hours', 'https://maps.google.com', 'ipoh_oldtown.jpg', 4.6, '2,400 reviews'],
            ['Perak', 'Culture', 'Kellie\'s Castle', 'Unfinished mystical Scottish mansion rich with tragic romance stories and hidden passages.', '2 Hours', 'https://maps.google.com', 'kellies_castle.jpg', 4.5, '1,980 reviews'],
            ['Perak', 'Culture', 'Kuala Kangsar Royal District', 'Admire beautiful classic palaces and the golden architecture of Ubudiah Mosque.', '2-3 Hours', 'https://maps.google.com', 'kualakangsar.jpg', 4.6, '450 reviews'],
            ['Perak', 'Culture', 'Matang Mangrove Charcoal Factory', 'Century-old smokehouse processing logs using heritage traditional burning styles.', '1 Hour', 'https://maps.google.com', 'charcoal_factory.jpg', 4.5, '320 reviews'],
            ['Perak', 'Food', 'Ipoh White Coffee & Dim Sum', 'Sample authentic locally brewed white coffee and delicious traditional handmade dim sum.', '1-2 Hours', 'https://maps.google.com', 'ipoh_coffee.jpg', 4.8, '3,100 reviews'],
            ['Perak', 'Food', 'Nasi Ganja Yong Suan', 'Traditional legendary spicy rice mixed with fragrant red curries and crispy chicken.', '1 Hour', 'https://maps.google.com', 'nasiganja.jpg', 4.9, '2,800 reviews'],

            // --- SELANGOR ---
            ['Selangor', 'Beach', 'Redang Beach Sekinchan', 'Unique coastal beach strip featuring old wishing trees and seaside wooden shacks.', '1-2 Hours', 'https://maps.google.com', 'redang_beach.jpg', 4.2, '910 reviews'],
            ['Selangor', 'Beach', 'Pantai Morib', 'Historical, nostalgic beach park perfect for breezy evening strolls and kite flying.', '2 Hours', 'https://maps.google.com', 'morib.jpg', 4.0, '1,500 reviews'],
            ['Selangor', 'Beach', 'Pantai Remis', 'Rocky shoreline offering beautiful sea views and dynamic sunset seafood setups.', '1-2 Hours', 'https://maps.google.com', 'morib.jpg', 4.1, '820 reviews'],
            ['Selangor', 'Beach', 'Pulau Ketam', 'Floating wooden fishing village built entirely over coastal mangrove mudflats.', '4-6 Hours', 'https://maps.google.com', 'pulau_ketam.jpg', 4.4, '1,100 reviews'],
            ['Selangor', 'Nature', 'Kampung Kuantan Fireflies', 'Magical night boat tour through mangrove rivers illuminated by thousands of synchronized fireflies.', '1-2 Hours', 'https://maps.google.com', 'fireflies.jpg', 4.7, '1,650 reviews'],
            ['Selangor', 'Nature', 'Kuala Selangor Nature Park', 'Expansive dynamic wetland forest rich with mudskippers, crabs, and silvered leaf monkeys.', '2-3 Hours', 'https://maps.google.com', 'ks_naturepark.jpg', 4.3, '780 reviews'],
            ['Selangor', 'Nature', 'Templer Park Rainforest', 'Serene jungle park with natural cascades and cold pools under towering limestone views.', '3-4 Hours', 'https://maps.google.com', 'templer_park.jpg', 4.4, '690 reviews'],
            ['Selangor', 'Nature', 'Sky Mirror Sasaran', 'Amazing sandbar phenomenon reflecting skies like a giant mirror during low tides.', '3-4 Hours', 'https://maps.google.com', 'skymirror.jpg', 4.7, '2,105 reviews'],
            ['Selangor', 'Adventure', 'Skytrex Shah Alam', 'Thrilling high-rope obstacle course suspended high within rainforest canopies.', '3 Hours', 'https://maps.google.com', 'skytrex.jpg', 4.6, '540 reviews'],
            ['Selangor', 'Adventure', 'Paragliding Jugra Hill', 'Unforgettable tandem flight soaring high over historic hills and river deltas.', '1-2 Hours', 'https://maps.google.com', 'jugra_paragliding.jpg', 4.7, '280 reviews'],
            ['Selangor', 'Adventure', 'Hiking Bukit Broga', 'Famous ridge hike offering spectacular sunrises over rolling fields of lalang grass.', '2-3 Hours', 'https://maps.google.com', 'broga_hill.jpg', 4.5, '1,890 reviews'],
            ['Selangor', 'Adventure', 'White Water Rafting Ulu Selangor', 'Exciting, hidden river rafting route providing adrenaline hits amidst untouched wilderness.', '4 Hours', 'https://maps.google.com', 'uluselangor_rafting.jpg', 4.6, '150 reviews'],
            ['Selangor', 'Culture', 'Batu Caves', 'Iconic steep rainbow stairways leading up into deep, ancient limestone temple caverns.', '2-3 Hours', 'https://maps.google.com', 'batucaves.jpg', 4.6, '5,400 reviews'],
            ['Selangor', 'Culture', 'Sultan Salahuddin Abdul Aziz Mosque', 'Magnificent Blue Mosque boasting one of the world\'s largest religious domes.', '1-2 Hours', 'https://maps.google.com', 'bluemosque.jpg', 4.8, '2,300 reviews'],
            ['Selangor', 'Culture', 'Mah Meri Cultural Village', 'Indigenous heritage community legendary for highly intricate, expressive woodcarvings.', '2-3 Hours', 'https://maps.google.com', 'mahmeri.jpg', 4.5, '310 reviews'],
            ['Selangor', 'Culture', 'Klang Royal Heritage Walk', 'Explore classic architectures detailing the rich royal history of Selangor.', '2-3 Hours', 'https://maps.google.com', 'klang_walk.jpg', 4.3, '420 reviews'],
            ['Selangor', 'Food', 'Kajang Satay Haji Samuri', 'Sample authentic wood-coal grilled satay skewers served with spicy peanut sauce.', '1-2 Hours', 'https://maps.google.com', 'satay_kajang.jpg', 4.7, '3,800 reviews'],
            ['Selangor', 'Food', 'Klang Bak Kut Teh', 'Traditional rich, aromatic herbal meat broth that originated in the port city of Klang.', '1-2 Hours', 'https://maps.google.com', 'klang_bkt.jpg', 4.6, '2,900 reviews'],

            // --- PULAU PINANG ---
            ['Pulau Pinang', 'Beach', 'Monkey Beach', 'Secluded sandy strip inside Penang National Park accessible only by boat or trek.', '3-5 Hours', 'https://maps.google.com', 'monkey_beach.jpg', 4.3, '980 reviews'],
            ['Pulau Pinang', 'Beach', 'Kerachut Beach', 'Untouched coastline hosting a rare meromictic lake and sea turtle hatchery.', '4-6 Hours', 'https://maps.google.com', 'kerachut.jpg', 4.6, '640 reviews'],
            ['Pulau Pinang', 'Beach', 'Batu Ferringhi', 'Vibrant coastal stretch offering active watersports and dynamic night bazaars.', '2-4 Hours', 'https://maps.google.com', 'batu_ferringhi.jpg', 4.4, '3,100 reviews'],
            ['Pulau Pinang', 'Beach', 'Teluk Kampi', 'The longest, most isolated deep beach zone inside the park limits, highly private.', '5-6 Hours', 'https://maps.google.com', 'telukkampi.jpg', 4.5, '220 reviews'],
            ['Pulau Pinang', 'Nature', 'The Habitat Penang Hill', 'World-class eco-tourism rainforest park with an architectural 360-degree canopy walkway.', '3-4 Hours', 'https://maps.google.com', 'thehabitat.jpg', 4.8, '2,400 reviews'],
            ['Pulau Pinang', 'Nature', 'Entopia Butterfly Farm', 'Massive indoor live paradise dome housing thousands of free-flying tropical butterflies.', '2-3 Hours', 'https://maps.google.com', 'entopia.jpg', 4.7, '1,950 reviews'],
            ['Pulau Pinang', 'Nature', 'Penang Botanic Gardens', 'Historic verdant landscape park set below high hills, known for its wild monkeys.', '1-2 Hours', 'https://maps.google.com', 'penang_botanic.jpg', 4.3, '1,100 reviews'],
            ['Pulau Pinang', 'Nature', 'Frog Hill Tasek Gelugor', 'Abandoned quarry site containing stunning, vibrant red clay landscapes and emerald pools.', '1-2 Hours', 'https://maps.google.com', 'froghill.jpg', 4.4, '540 reviews'],
            ['Pulau Pinang', 'Adventure', 'Escape Theme Park', 'Home to the world\'s longest jungle water slide and extreme ropes courses.', '5-8 Hours', 'https://maps.google.com', 'escapethemepark.jpg', 4.9, '3,450 reviews'],
            ['Pulau Pinang', 'Adventure', 'Hiking to Penang Hill via Heritage Trail', 'Demanding uphill hike following the route of the historic funicular train track.', '3-4 Hours', 'https://maps.google.com', 'penanghill_hike.jpg', 4.6, '890 reviews'],
            ['Pulau Pinang', 'Adventure', 'ATV Balik Pulau', 'Ride through scenic countryside, local paddy fields, and traditional mangrove margins.', '1-2 Hours', 'https://maps.google.com', 'balikpulau_atv.jpg', 4.7, '230 reviews'],
            ['Pulau Pinang', 'Adventure', 'Jet Skiing Batu Ferringhi', 'High-speed water adventure tracking the expansive coastline of Penang.', '1 Hour', 'https://maps.google.com', 'jetski.jpg', 4.3, '310 reviews'],
            ['Pulau Pinang', 'Culture', 'George Town Heritage Murals', 'Hunting world-famous interactive street art pieces curated by Ernest Zacharevic.', '2-4 Hours', 'https://maps.google.com', 'penang_murals.jpg', 4.7, '4,200 reviews'],
            ['Pulau Pinang', 'Culture', 'Kek Lok Si Temple', 'Grandest Buddhist temple complex in Southeast Asia, with a giant bronze Guanyin statue.', '2-3 Hours', 'https://maps.google.com', 'kekloksi.jpg', 4.7, '3,200 reviews'],
            ['Pulau Pinang', 'Culture', 'Cheong Fatt Tze Blue Mansion', 'Award-winning indigo-blue heritage Chinese courtyard home of a prominent historian.', '1-2 Hours', 'https://maps.google.com', 'bluemansion.jpg', 4.6, '1,500 reviews'],
            ['Pulau Pinang', 'Culture', 'Clan Jetties', 'Historic 19th-century Chinese waterfront settlements built fully on high wooden stilts.', '1-2 Hours', 'https://maps.google.com', 'clanjetties.jpg', 4.4, '1,800 reviews'],
            ['Pulau Pinang', 'Food', 'George Town Street Food Trail', 'Famous local hawker food tour including Char Kway Teow, Penang Laksa, and Cendol.', '1-2 Hours', 'https://maps.google.com', 'penang_food.jpg', 4.9, '5,200 reviews'],
            ['Pulau Pinang', 'Food', 'Line Clear Nasi Kandar', 'Historical legendary local Indian-Muslim restaurant offering robust curry assortments.', '1 Hour', 'https://maps.google.com', 'nasikandar.jpg', 4.8, '4,100 reviews'],

            // --- PAHANG ---
            ['Pahang', 'Beach', 'Juara Beach Tioman', 'Quiet, peaceful golden bay on the eastern flank of Tioman Island with clean streams.', '4-8 Hours', 'https://maps.google.com', 'juara.jpg', 4.7, '450 reviews'],
            ['Pahang', 'Beach', 'Pantai Teluk Cempedak', 'Iconic white sand bay offering raised wooden walkways winding around rocky headlands.', '2-3 Hours', 'https://maps.google.com', 'cempedak.jpg', 4.4, '2,100 reviews'],
            ['Pahang', 'Beach', 'Pantai Cherating', 'Famed cultural surf beach spot known for its laidback village soul and turtle sanctuaries.', '3-5 Hours', 'https://maps.google.com', 'cherating.jpg', 4.5, '1,300 reviews'],
            ['Pahang', 'Beach', 'Monkey Bay Tioman', 'Stunning hidden desert oasis shaped like an hourglass, exceptional for snorkeling.', '3-4 Hours', 'https://maps.google.com', 'mokey_bay.jpg', 4.6, '320 reviews'],
            ['Pahang', 'Nature', 'Taman Negara Canopy Walk', 'Walk on the world\'s longest canopy bridge system built deep within ancient rainforests.', '3-5 Hours', 'https://maps.google.com', 'tamannegara.jpg', 4.6, '1,450 reviews'],
            ['Pahang', 'Nature', 'Mossy Forest Brinchang', 'Surreal cloud forest landscape covered entirely in thick green moss, mist, and lichens.', '2 Hours', 'https://maps.google.com', 'mossyforest.jpg', 4.7, '1,678 reviews'],
            ['Pahang', 'Nature', 'Sungai Chiling Waterfall', 'Scenic river trail requiring multiple stream crossings leading to an incredible river pool.', '4-5 Hours', 'https://maps.google.com', 'chiling.jpg', 4.6, '810 reviews'],
            ['Pahang', 'Nature', 'Tasik Chini', 'Mystical natural freshwater lake rich with summer lotus blooms.', '3-4 Hours', 'https://maps.google.com', 'chini.jpg', 4.1, '340 reviews'],
            ['Pahang', 'Adventure', 'Cameron Highlands Mossy Trekking', 'Rugged hiking up slippery muddy slopes mapping high mistry ridge trails.', '3-5 Hours', 'https://maps.google.com', 'cameron_trek.jpg', 4.5, '620 reviews'],
            ['Pahang', 'Adventure', 'Rapid Shooting Taman Negara', 'Thrilling wooden boat ride negotiating swirling, splashing white-water river rapids.', '2 Hours', 'https://maps.google.com', 'rapid_shooting.jpg', 4.6, '410 reviews'],
            ['Pahang', 'Adventure', 'Scuba Diving Tioman', 'Explore deep marine parks filled with sea turtles, reef sharks, and coral gardens.', '4-6 Hours', 'https://maps.google.com', 'tioman_dive.jpg', 4.8, '1,100 reviews'],
            ['Pahang', 'Adventure', 'Hiking Bukit Panorama', 'Early morning hill climb to capture an ocean of morning clouds over historic towns.', '2 Hours', 'https://maps.google.com', 'panorama_hill.jpg', 4.7, '530 reviews'],
            ['Pahang', 'Culture', 'Sungai Palas Tea Garden', 'Stunning futuristic cafe cantilevered over endless rolling hills of tea plantations.', '2-3 Hours', 'https://maps.google.com', 'sungaipalas.jpg', 4.7, '2,800 reviews'],
            ['Pahang', 'Culture', 'Kuala Gandah Elephant Sanctuary', 'Ecotourism center focused on rescuing, rehabilitating, and protecting Asian elephants.', '3-4 Hours', 'https://maps.google.com', 'elephants.jpg', 4.5, '1,200 reviews'],
            ['Pahang', 'Culture', 'Sungai Lembing Underground Mines', 'Step inside what was once the largest and deepest underground tin mine in the world.', '2 Hours', 'https://maps.google.com', 'lembing_mines.jpg', 4.4, '650 reviews'],
            ['Pahang', 'Culture', 'Raub Old Town Traditional Street', 'Quaint colonial heritage streets filled with historic traditional coffee shops.', '1-2 Hours', 'https://maps.google.com', 'raub_town.jpg', 4.3, '180 reviews'],
            ['Pahang', 'Food', 'Bentong Ginger & Durian Trail', 'Enjoy authentic Bentong ginger dishes, hand-churned ice cream, and fresh Musang King durians.', '1-2 Hours', 'https://maps.google.com', 'bentong_food.jpg', 4.8, '1,500 reviews'],
            ['Pahang', 'Food', 'Cameron Highland Strawberry Waffles', 'Fresh local strawberries paired with warm waffles and tea at Boh Plantation farms.', '1-2 Hours', 'https://maps.google.com', 'strawberry_waffles.jpg', 4.7, '2,100 reviews'],

            // --- JOHOR ---
            ['Johor', 'Beach', 'Rawa Island (Pulau Mawar)', 'Private island paradise offering unparalleled seclusion and beautiful crystal-clear coral horizons.', '5-8 Hours', 'https://maps.google.com', 'pulaumawar.jpeg', 4.7, '1,520 reviews'],
            ['Johor', 'Beach', 'Desaru Coast Beach', 'Wide premium shoreline featuring premium family resorts and clear coastlines.', '3-4 Hours', 'https://maps.google.com', 'desaru.jpeg', 4.5, '1,980 reviews'],
            ['Johor', 'Beach', 'Sibu Island (Pulau Sibu)', 'Quiet tropical escape ideal for spotting golden sand lines and shallow coral diving.', '4-6 Hours', 'https://maps.google.com', 'sibu.jpeg', 4.4, '840 reviews'],
            ['Johor', 'Beach', 'Pantai Minyak Beku', 'Historical beach site offering serene sunset strolls and a unique local monument backstory.', '1-2 Hours', 'https://maps.google.com', 'minyakbeku.jpg', 4.2, '540 reviews'],
            ['Johor', 'Nature', 'Endau-Rompin National Park', 'Explore one of the oldest ancient tropical rainforest networks inside Peninsular Malaysia.', '1-2 Days', 'https://maps.google.com', 'endau_rompin.jpeg', 4.8, '820 reviews'],
            ['Johor', 'Nature', 'Gunung Pulai Recreational Forest', 'Popular local mountain trek offering refreshing natural streams and multi-tiered waterfall pools.', '3-4 Hours', 'https://maps.google.com', 'gunung_pulai.jpg', 4.4, '940 reviews'],
            ['Johor', 'Nature', 'Tanjung Piai Mangrove Park', 'Walk down wooden paths traversing thick coastal swamp forests to visit the southernmost point of Mainland Asia.', '2-3 Hours', 'https://maps.google.com', 'tanjung_piai.jpg', 4.5, '1,120 reviews'],
            ['Johor', 'Nature', 'Kota Tinggi Firefly Park', 'Magical evening river cruise tracking thousands of blinking fireflies reflecting on river canals.', '1-2 Hours', 'https://maps.google.com', 'kotatinggi_fireflies.jpg', 4.6, '720 reviews'],
            ['Johor', 'Adventure', 'LegoLand Malaysia Theme Park', 'High-octane amusement rides, immersive waterpark slides, and complex block architecture models.', '5-8 Hours', 'https://maps.google.com', 'legoland.jpeg', 4.8, '3,800 reviews'],
            ['Johor', 'Adventure', 'Austin Heights Water Adventure', 'Thrilling high-rope obstacle courses, zip-lines, and large water-fun drop points.', '4-5 Hours', 'https://maps.google.com', 'austin_heights.jpeg', 4.5, '1,020 reviews'],
            ['Johor', 'Adventure', 'Hiking Bukit Selantai', 'Challenging hill scramble providing a reward of breathtaking panoramic sea views from the peak line.', '2-3 Hours', 'https://maps.google.com', 'selantai.jpeg', 4.4, '310 reviews'],
            ['Johor', 'Adventure', 'ATV Park Johor Bahru', 'Get muddy riding powerful off-road vehicles through deep jungle trails and rugged tracks.', '1-2 Hours', 'https://maps.google.com', 'johor_atv.jpg', 4.6, '450 reviews'],
            ['Johor', 'Culture', 'Johor Bahru City Centre (Heritage Walk)', 'Tour old colonial lanes, classic shophouses, and ancient temples tucked inside the city limits.', '2-3 Hours', 'https://maps.google.com', 'default_place.jpg', 4.5, '1,150 reviews'],
            ['Johor', 'Culture', 'Muar Historical Town', 'Renowned royal town iconic for classic pre-war layout arts and famous local coffee houses.', '3-4 Hours', 'https://maps.google.com', 'muar_town.jpeg', 4.6, '890 reviews'],
            ['Johor', 'Culture', 'Sultan Abu Bakar State Mosque', 'Stunning 19th-century architecture blending Victorian aesthetics with classic Moorish designs.', '1 Hour', 'https://maps.google.com', 'sultan_mosque.jpeg', 4.8, '1,650 reviews'],
            ['Johor', 'Culture', 'Tan Hiok Nee Heritage Street', 'Vibrant cultural hub filled with traditional bakeries, murals, and old Chinese clan spaces.', '1-2 Hours', 'https://maps.google.com', 'tanhioknee.jpeg', 4.7, '1,980 reviews'],
            ['Johor', 'Food', 'Muar Mee Bandung & Otak-Otak', 'Sample Muar\'s signature thick egg noodle broth and savory charcoal-grilled fish paste.', '1 Hour', 'https://maps.google.com', 'muar_food.jpeg', 4.8, '1,420 reviews'],
            ['Johor', 'Food', 'Larkin Kacang Pool Haji', 'Traditional Middle Eastern inspired local bean paste stew topped with sunny side egg and toast.', '1 Hour', 'https://maps.google.com', 'kacang_pool.jpeg', 4.7, '1,200 reviews'],

            // --- SABAH ---
            ['Sabah', 'Beach', 'Sipadan Island Marine Park', 'Globally celebrated diving destination hosting thousands of sea turtles and swirling barracuda walls.', '5-8 Hours', 'https://maps.google.com', 'sipadan.png', 4.9, '2,410 reviews'],
            ['Sabah', 'Beach', 'Mabul Island Vista', 'Incredible white sand sandbars looking down directly into premium shallow coral gardens.', '4-6 Hours', 'https://maps.google.com', 'mabul.jpeg', 4.7, '1,800 reviews'],
            ['Sabah', 'Beach', 'Tanjung Aru Beach', 'Famous sunset coastal strip providing beautiful orange horizons alongside local food centers.', '2 Hours', 'https://maps.google.com', 'tanjung_aru.jpeg', 4.5, '3,100 reviews'],
            ['Sabah', 'Beach', 'Mantanani Islands', 'Isolated clear water islands perfect for peaceful swimming and spotting rare marine life.', '5-8 Hours', 'https://maps.google.com', 'mantanani.jpg', 4.6, '740 reviews'],
            ['Sabah', 'Nature', 'Mount Kinabalu & Kundasang Highlands', 'Breathtaking cool valleys set against the backdrop of Malaysia\'s highest majestic peak.', '1-2 Days', 'https://maps.google.com', 'kundasang.jpeg', 4.9, '5,200 reviews'],
            ['Sabah', 'Nature', 'Sepilok Orangutan Sanctuary', 'Observe orphaned wild orangutans learning survival steps on specialized forest platforms.', '2-3 Hours', 'https://maps.google.com', 'sepilok.jpeg', 4.7, '2,600 reviews'],
            ['Sabah', 'Nature', 'Danum Valley Conservation Area', 'Untouched ancient primary rainforest ecosystem tracking rare pygmy elephants and leopards.', '1-2 Days', 'https://maps.google.com', 'danum_valley.jpeg', 4.8, '540 reviews'],
            ['Sabah', 'Nature', 'Kinabatangan River Cruise', 'Relaxing boat expedition searching for proboscis monkeys and crocodiles along muddy riverbanks.', '3-4 Hours', 'https://maps.google.com', 'kinabatangan.jpeg', 4.6, '1,420 reviews'],
            ['Sabah', 'Adventure', 'Kiulu River White Water Rafting', 'Gentle yet exciting river rapid rafting route ideal for capturing scenic valley views.', '3 Hours', 'https://maps.google.com', 'kiulu.jpeg', 4.5, '980 reviews'],
            ['Sabah', 'Adventure', 'Padas River Extreme Rafting', 'High-adrenaline white-water route negotiating rough class III-IV rapids inside deep gorges.', '4-5 Hours', 'https://maps.google.com', 'padas.jpeg', 4.6, '420 reviews'],
            ['Sabah', 'Adventure', 'Via Ferrata Kinabalu', 'The world\'s highest alpine mountain rock walkway route utilizing specialized secure wire lines.', '5-8 Hours', 'https://maps.google.com', 'via_ferrata.jpeg', 4.8, '630 reviews'],
            ['Sabah', 'Adventure', 'Hiking Maragang Hill', 'Rewarding early-morning hike to witness Kinabalu\'s stone peaks emerging above low clouds.', '3-4 Hours', 'https://maps.google.com', 'maragang.jpeg', 4.7, '340 reviews'],
            ['Sabah', 'Culture', 'Gaya Street Sunday Market', 'Bustling historical market displaying unique local items, handicrafts, and Bornean snacks.', '2-3 Hours', 'https://maps.google.com', 'gayastreet.jpg', 4.5, '2,400 reviews'],
            ['Sabah', 'Culture', 'Mari Mari Cultural Village', 'Interactive tribal settlement showcasing traditional houses, fire-starting, and blowpipe skills.', '3-4 Hours', 'https://maps.google.com', 'marimari.jpeg', 4.8, '1,890 reviews'],
            ['Sabah', 'Culture', 'Monsopiad Heritage Village', 'Step inside a historical site chronicling the legendary stories of Kadazan headhunters.', '2 Hours', 'https://maps.google.com', 'monsopiad.jpeg', 4.4, '310 reviews'],
            ['Sabah', 'Culture', 'Sabah State Museum & Heritage Village', 'Expansive archives documenting ancestral archaeological pottery and native longhouses.', '2 Hours', 'https://maps.google.com', 'sabah_museum.jpg', 4.3, '780 reviews'],
            ['Sabah', 'Food', 'Tuaran Mee & Sabah Seafood', 'Taste classic egg noodles fried with pork char siew and fresh lobster/shrimp platters.', '1-2 Hours', 'https://maps.google.com', 'tuaran_mee.jpg', 4.8, '1,680 reviews'],
            ['Sabah', 'Food', 'Beaufort Nasi Penyet & Tenom Coffee', 'Sample crispy deep-fried chicken paired with famous aromatic wood-roasted Tenom coffee.', '1 Hour', 'https://maps.google.com', 'tenom_coffee.jpeg', 4.7, '1,100 reviews'],

            // --- SARAWAK ---
            ['Sarawak', 'Beach', 'Damai Beach Resort Strip', 'Scenic sandy stretch sitting below the shadow of Mount Santubong\'s rainforest edges.', '3-5 Hours', 'https://maps.google.com', 'damai_beach.jpeg', 4.4, '1,120 reviews'],
            ['Sarawak', 'Beach', 'Tusan Cliff Beach', 'Dramatic limestone cliffs famously known for bright blue-tears ocean bioluminescence events.', '2 Hours', 'https://maps.google.com', 'tusan.jpeg', 4.5, '940 reviews'],
            ['Sarawak', 'Beach', 'Talang-Talang Islands', 'Protected marine boundaries serving as sanctuaries for endangered green sea turtles.', '4-6 Hours', 'https://maps.google.com', 'talang.jpeg', 4.6, '310 reviews'],
            ['Sarawak', 'Beach', 'Pantai Pasir Panjang', 'Quiet, uncrowded beachfront ideal for observing serene evening tide patterns.', '2 Hours', 'https://maps.google.com', 'pasirpanjang.jpg', 4.1, '430 reviews'],
            ['Sarawak', 'Nature', 'Mulu Caves National Park', 'UNESCO treasure hosting the world\'s largest cave chambers and massive bat migrations.', '1-2 Days', 'https://maps.google.com', 'guaniah.jpeg', 4.9, '1,980 reviews'],
            ['Sarawak', 'Nature', 'Bako National Park Trails', 'Sarawak\'s oldest park, famous for strange stone sea-stacks and wild proboscis monkeys.', '4-6 Hours', 'https://maps.google.com', 'bako.jpg', 4.8, '2,100 reviews'],
            ['Sarawak', 'Nature', 'Semenggoh Wildlife Centre', 'Established sanctuary to witness majestic semi-wild orangutans coming down for fruit feeds.', '2 Hours', 'https://maps.google.com', 'semenggoh.jpeg', 4.7, '1,450 reviews'],
            ['Sarawak', 'Nature', 'Niah National Park Caves', 'Massive limestone structures tracking prehistoric human settlement remains dating back 40,000 years.', '4-5 Hours', 'https://maps.google.com', 'niah.jpeg', 4.6, '890 reviews'],
            ['Sarawak', 'Adventure', 'Mulu Pinnacles Climb', 'Demanding multi-day vertical trek exploring razor-sharp limestone rock spires rising over tree canopies.', '2 Days', 'https://maps.google.com', 'mulu_pinnacles.jpeg', 4.8, '340 reviews'],
            ['Sarawak', 'Adventure', 'Santubong River Kayaking', 'Paddle through winding mangrove streams searching for rare Irrawaddy river dolphins.', '3-4 Hours', 'https://maps.google.com', 'santubong_kayak.jpeg', 4.5, '540 reviews'],
            ['Sarawak', 'Adventure', 'Bengkoh Lake Kayak Eco Tour', 'Glide across clear, calm mountain reservoir waters past flooded ghost forest trees.', '4 Hours', 'https://maps.google.com', 'bengkoh_lake.jpeg', 4.7, '220 reviews'],
            ['Sarawak', 'Adventure', 'Hiking Mount Santubong Peak', 'Strenuous vertical climb utilizing rope ladders and muddy root holds to reach high panoramic viewpoints.', '5-7 Hours', 'https://maps.google.com', 'santubong_peak.jpeg', 4.6, '610 reviews'],
            ['Sarawak', 'Culture', 'Kuching Waterfront & Old Court', 'Stroll down historic river paths adjacent to traditional colonial administrative hubs.', '2-3 Hours', 'https://maps.google.com', 'kuching_waterfront.jpg', 4.6, '2,800 reviews'],
            ['Sarawak', 'Culture', 'Sarawak Cultural Village', 'Living museum showing true lifestyle setups of 7 distinct local tribal communities.', '3-4 Hours', 'https://maps.google.com', 'sarawak_cultural.jpg', 4.8, '2,100 reviews'],
            ['Sarawak', 'Culture', 'Sibu Night Market Hub', 'Energetic market row serving authentic Foochow street food, snacks, and steamed buns.', '1-2 Hours', 'https://maps.google.com', 'sibu_market.jpeg', 4.5, '1,120 reviews'],
            ['Sarawak', 'Culture', 'Borneo Cultures Museum', 'State-of-the-art exhibition center displaying precious historic artifacts and native textiles.', '2-3 Hours', 'https://maps.google.com', 'borneo_museum.jpeg', 4.8, '1,980 reviews'],
            ['Sarawak', 'Food', 'Sarawak Laksa & Kolo Mee', 'Taste Kuching\'s legendary herbal spicy laksa noodle broth and dry tossed minced meat noodles.', '1-2 Hours', 'https://maps.google.com', 'sarawak_laksa.jpeg', 4.9, '3,900 reviews'],
            ['Sarawak', 'Food', 'Ayam Pansuh & Midin Ferns', 'Authentic tribal bamboo-cooked chicken and crispy jungle ferns stir-fried with shrimp paste.', '1-2 Hours', 'https://maps.google.com', 'pansuh.jpeg', 4.8, '920 reviews'],

            // --- KEDAH ---
            ['Kedah', 'Beach', 'Langkawi Island (Pantai Cenang)', 'Kedah\'s premier lively coastline offering soft sand beds and exciting water sport options.', '3-5 Hours', 'https://maps.google.com', 'langkawi.jpeg', 4.6, '1,740 reviews'],
            ['Kedah', 'Beach', 'Tanjung Rhu Beach Hideout', 'Serene, premium beach strip flanked by unique limestone islands and calm waters.', '2-3 Hours', 'https://maps.google.com', 'tanjung_rhu.jpeg', 4.7, '1,200 reviews'],
            ['Kedah', 'Beach', 'Pantai Tengah Beach', 'Peaceful alternative to Cenang, ideal for catching quiet, unobstructed sunset views.', '2 Hours', 'https://maps.google.com', 'pantai_tengah.jpg', 4.4, '830 reviews'],
            ['Kedah', 'Beach', 'Pulau Payar Marine Park', 'Exceptional island sanctuary hosting shallow coral reefs and encounters with baby blacktip sharks.', '5-8 Hours', 'https://maps.google.com', 'pulau_payar.jpeg', 4.5, '940 reviews'],
            ['Kedah', 'Nature', 'Kilim Geoforest Mangrove Tour', 'Boat safari through ancient limestone mangrove gorges to observe wild eagles feeding.', '2-3 Hours', 'https://maps.google.com', 'kilim.jpeg', 4.7, '1,890 reviews'],
            ['Kedah', 'Nature', 'Mount Jerai Resort Peak (Gunung Jerai)', 'Drive up a cool mountain peak overlooking endless emerald paddy field layouts.', '3-4 Hours', 'https://maps.google.com', 'gunung_jerai.jpg', 4.5, '1,100 reviews'],
            ['Kedah', 'Nature', 'Telaga Tujuh Seven Wells Waterfall', 'Natural freshwater pools fed by seven connected mountain streams deep in the jungle.', '2-3 Hours', 'https://maps.google.com', 'seven_wells.jpeg', 4.6, '1,320 reviews'],
            ['Kedah', 'Nature', 'Ulu Muda Forest Reserve', 'Deep, pristine wilderness shelter tracking wild elephants, salt licks, and rare birds.', '1-2 Days', 'https://maps.google.com', 'ulu_muda.jpeg', 4.5, '280 reviews'],
            ['Kedah', 'Adventure', 'Langkawi SkyCab & SkyBridge Walkway', 'Ride a high cable car to step on a dramatic curved suspension bridge hanging over canyons.', '3-4 Hours', 'https://maps.google.com', 'skybridge.jpeg', 4.8, '4,100 reviews'],
            ['Kedah', 'Adventure', 'Jet Ski Island Hopping Safari', 'High-speed personal watercraft tour navigating around hidden rock formations and lake islands.', '3-4 Hours', 'https://maps.google.com', 'jetski_safari.jpeg', 4.7, '780 reviews'],
            ['Kedah', 'Adventure', 'Skytrex Adventure Langkawi', 'Challenging tree-to-tree obstacle rope courses suspended inside a dense rainforest canopy.', '3 Hours', 'https://maps.google.com', 'skytrex_langkawi.jpeg', 4.6, '430 reviews'],
            ['Kedah', 'Adventure', 'Ziplining Umgawa Eco Adventures', 'Fly along high-speed steel cable courses tracking pristine waterfall gorge viewpoints.', '2-3 Hours', 'https://maps.google.com', 'umgawa.jpeg', 4.7, '310 reviews'],
            ['Kedah', 'Culture', 'Alor Setar Paddy Museum & Tower', 'Unique cultural venue detailing historical rice cultivation alongside a panoramic tower view.', '2-3 Hours', 'https://maps.google.com', 'paddy_museum.jpg', 4.4, '650 reviews'],
            ['Kedah', 'Culture', 'Lembah Bujang Archaeological Site', 'Explore the ruins of a Hindu-Buddhist kingdom dating back over 2,000 years.', '2 Hours', 'https://maps.google.com', 'lembah_bujang.jpeg', 4.5, '450 reviews'],
            ['Kedah', 'Culture', 'Mahsuri Cultural Tomb Centre', 'Historical courtyard complex documenting the famous tragic legend of Langkawi\'s folklore.', '1-2 Hours', 'https://maps.google.com', 'mahsuri.jpeg', 4.4, '1,100 reviews'],
            ['Kedah', 'Culture', 'Zahir Mosque Historic Architecture', 'One of Malaysia\'s oldest and finest Moorish-style religious structures with black domes.', '1 Hour', 'https://maps.google.com', 'zahir_mosque.jpeg', 4.8, '1,950 reviews'],
            ['Kedah', 'Food', 'Alor Setar Nasi Lemak Royale', 'Classic yellow rice paired with rich, sweet-spicy thick gravies and assorted side dishes.', '1 Hour', 'https://maps.google.com', 'lemak_royale.jpeg', 4.8, '2,300 reviews'],
            ['Kedah', 'Food', 'Laksa Kedah Teluk Kechai', 'Fragrant rice noodles served in thick spicy fish broth topped with local coconut sambal.', '1 Hour', 'https://maps.google.com', 'laksa_kedah.jpeg', 4.7, '1,900 reviews'],

            // --- KELANTAN ---
            ['Kelantan', 'Beach', 'Pantai Cahaya Bulan (PCB Beach)', 'Famous local beach lined with traditional snack stalls serving fresh seafood.', '1-2 Hours', 'https://maps.google.com', 'gunungkelantan.png', 4.7, '1,310 reviews'],
            ['Kelantan', 'Beach', 'Pantai Senok (Pine Tree Forest Beach)', 'Beautiful coastal stretch closely packed with tall casuarina trees resembling a Nami Island look.', '1-2 Hours', 'https://maps.google.com', 'pantaisenok.jpeg', 4.4, '1,500 reviews'],
            ['Kelantan', 'Beach', 'Pantai Melawi Shoreline', 'Tranquil sandy beach ideal for relaxing evening walks away from crowds.', '2 Hours', 'https://maps.google.com', 'default_place.jpg', 4.1, '430 reviews'],
            ['Kelantan', 'Beach', 'Pantai Sri Tujuh Lagoon', 'Unique coastal border zone hosting large artificial lagoons and regular boat festivals.', '2 Hours', 'https://maps.google.com', 'sritujuh.jpeg', 4.3, '890 reviews'],
            ['Kelantan', 'Nature', 'Gunung Stong State Park Wilds', 'Home to the massive seven-tiered Jelawang Waterfall, one of the highest in Southeast Asia.', '1-2 Days', 'https://maps.google.com', 'stong.jpeg', 4.8, '540 reviews'],
            ['Kelantan', 'Nature', 'Lata Rek Eco Forest Waterfall', 'Popular tiered rocky cascade forming refreshing natural mountain river pools.', '2-3 Hours', 'https://maps.google.com', 'latarek.jpeg', 4.4, '650 reviews'],
            ['Kelantan', 'Nature', 'Gua Ikan Limestone Caves', 'Fascinating natural cavern system named after unique fish-shaped entrance rock alignments.', '2 Hours', 'https://maps.google.com', 'guaikan.jpeg', 4.3, '310 reviews'],
            ['Kelantan', 'Nature', 'Bukit Marak Mountain Trails', 'Historic hill climb tracking local princess legends and panoramic countryside views.', '2 Hours', 'https://maps.google.com', 'bukit_marak.jpeg', 4.2, '450 reviews'],
            ['Kelantan', 'Adventure', 'Nenggiri River Adventure Trail', 'Exciting bamboo rafting or kayaking trip through historic limestone caves and rainforests.', '4-6 Hours', 'https://maps.google.com', 'nenggiri.jpg', 4.7, '340 reviews'],
            ['Kelantan', 'Adventure', 'Hiking Gunung Stong Peak', 'Demanding mountain trek scaling slippery rock steep zones to camp above clouds.', '5-8 Hours', 'https://maps.google.com', 'stong_hiking.jpeg', 4.6, '220 reviews'],
            ['Kelantan', 'Adventure', 'Jungle Railway Train Experience', 'Scenic rural diesel train trip navigating through small inland settlements and forest bridges.', '3-5 Hours', 'https://maps.google.com', 'jungle_railway.jpeg', 4.5, '540 reviews'],
            ['Kelantan', 'Adventure', 'River Tubing Dabong', 'Fun, splashing ride floating down clear mountain streams wrapped in forest greenery.', '2-3 Hours', 'https://maps.google.com', 'dabong_tubing.jpeg', 4.6, '310 reviews'],
            ['Kelantan', 'Culture', 'Siti Khadijah Market', 'Vibrant octagonal market hub famously run mostly by enterprising local women traders.', '2-3 Hours', 'https://maps.google.com', 'sitikhadijah.jpg', 4.7, '3,200 reviews'],
            ['Kelantan', 'Culture', 'Wat Photivihan (Sleeping Buddha)', 'Impressive temple complex hosting a massive 40-meter reclining Buddha statue structure.', '1 Hour', 'https://maps.google.com', 'photivihan.jpeg', 4.6, '1,450 reviews'],
            ['Kelantan', 'Culture', 'Kampung Wau Craft Artisan Workshop', 'Watch master craftsmen assemble large traditional Malaysian kites (Wau Bulan) using paper filigree.', '1-2 Hours', 'https://maps.google.com', 'wau_craft.jpeg', 4.8, '540 reviews'],
            ['Kelantan', 'Culture', 'Istana Jahar (Customs Museum)', 'Beautiful wooden royal palace detailing detailed classic carvings and traditional ceremonies.', '1-2 Hours', 'https://maps.google.com', 'istana_jahar.jpeg', 4.5, '720 reviews'],
            ['Kelantan', 'Food', 'Nasi Kerabu Siti Khadijah', 'Traditional blue-colored rice infused with wild herbs, served with salted fish, coconut shreds, and solok lada.', '1 Hour', 'https://maps.google.com', 'nasikerabu.jpeg', 4.9, '3,600 reviews'],
            ['Kelantan', 'Food', 'Laksam & Akok Kelantan', 'Steamed rice rolls in creamy white fish gravy paired with sweet, rich baked duck-egg akok custards.', '1 Hour', 'https://maps.google.com', 'laksam_akok.jpeg', 4.8, '2,200 reviews']
        ];

        // Seed inside a database transaction block for maximum speed and integrity
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO `attractions` (`state`, `category`, `name`, `description`, `recommended_time`, `maps_link`, `image_url`, `rating`, `reviews_count`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($initial_gems as $gem) {
            $stmt->execute($gem);
        }
        $pdo->commit();
    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

// 2. QUERY DATABASE USING PDO PREPARED STATEMENTS
try {
    $stmt = $pdo->prepare("SELECT * FROM attractions");
    $stmt->execute();
    $db_attractions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map the DB attractions into the JS format structure dynamically
    $js_gems_db = [];
    foreach ($db_attractions as $attr) {
        $st = $attr['state'];
        $cat = $attr['category'];
        if (!isset($js_gems_db[$st])) {
            $js_gems_db[$st] = [
                'image' => $attr['image_url'],
                'description' => "Explore the beautiful destinations, activities, and hidden gems of " . $st . ".",
                'rating' => '4.7',
                'reviews' => '1,200 reviews',
                'gems' => [
                    'Beach' => [],
                    'Nature' => [],
                    'Adventure' => [],
                    'Culture' => [],
                    'Food' => []
                ]
            ];
        }

        // Apply visual branding characteristics per state
        if ($st == 'Terengganu') { $js_gems_db[$st]['image'] = 'terrapuri.png'; $js_gems_db[$st]['rating'] = '4.8'; $js_gems_db[$st]['reviews'] = '1,240 reviews'; $js_gems_db[$st]['description'] = 'Crystal-clear waters, pristine beaches, and rich Malay heritage await you in the east coast gem of Malaysia.'; }
        else if ($st == 'Perak') { $js_gems_db[$st]['image'] = 'coralbeach.jpg'; $js_gems_db[$st]['rating'] = '4.6'; $js_gems_db[$st]['reviews'] = '987 reviews'; $js_gems_db[$st]['description'] = 'From the royal town of Kuala Kangsar to the limestone wonders of Ipoh, Perak blends history with natural splendor.'; }
        else if ($st == 'Selangor') { $js_gems_db[$st]['image'] = 'skymirror.jpg'; $js_gems_db[$st]['rating'] = '4.5'; $js_gems_db[$st]['reviews'] = '2,105 reviews'; $js_gems_db[$st]['description'] = 'Beyond the city lights, Selangor hides firefly sanctuaries, ancient forests, and coastal fishing villages.'; }
        else if ($st == 'Pulau Pinang') { $js_gems_db[$st]['image'] = 'escapethemepark.jpg'; $js_gems_db[$st]['rating'] = '4.9'; $js_gems_db[$st]['reviews'] = '3,450 reviews'; $js_gems_db[$st]['description'] = 'A UNESCO heritage city famed for street art, legendary hawker food, and a unique blend of colonial and Asian culture.'; }
        else if ($st == 'Pahang') { $js_gems_db[$st]['image'] = 'pulautioman.png'; $js_gems_db[$st]['rating'] = '4.7'; $js_gems_db[$st]['reviews'] = '1,678 reviews'; $js_gems_db[$st]['description'] = 'Home to Taman Negara, the world\'s oldest rainforest, and the cool highland retreats of Cameron and Genting.'; }
        else if ($st == 'Johor') { $js_gems_db[$st]['image'] = 'pulaumawar.png'; $js_gems_db[$st]['rating'] = '4.7'; $js_gems_db[$st]['reviews'] = '1,520 reviews'; $js_gems_db[$st]['description'] = 'A southern wonderland filled with diverse theme parks, pristine private marine islands, and delicious heritage food tracks.'; }
        else if ($st == 'Sabah') { $js_gems_db[$st]['image'] = 'kundasang.png'; $js_gems_db[$st]['rating'] = '4.9'; $js_gems_db[$st]['reviews'] = '2,410 reviews'; $js_gems_db[$st]['description'] = 'From the majestic peaks of Mount Kinabalu to world-class deep dive points, Sabah delivers ultimate biological diversity.'; }
        else if ($st == 'Sarawak') { $js_gems_db[$st]['image'] = 'guaniah.png'; $js_gems_db[$st]['rating'] = '4.8'; $js_gems_db[$st]['reviews'] = '1,980 reviews'; $js_gems_db[$st]['description'] = 'The land of the hornbills hides sprawling cave systems, wild orangutan sanctuaries, and deep cultural rivers.'; }
        else if ($st == 'Kedah') { $js_gems_db[$st]['image'] = 'langkawi.png'; $js_gems_db[$st]['rating'] = '4.6'; $js_gems_db[$st]['reviews'] = '1,740 reviews'; $js_gems_db[$st]['description'] = 'Known as the rice bowl of Malaysia, combining vast emerald paddy plains with the magical islands of Langkawi.'; }
        else if ($st == 'Kelantan') { $js_gems_db[$st]['image'] = 'gunungkelantan.png'; $js_gems_db[$st]['rating'] = '4.7'; $js_gems_db[$st]['reviews'] = '1,310 reviews'; $js_gems_db[$st]['description'] = 'The cradle of Malay culture, showcasing vibrant traditional markets, giant kite artisan crafts, and delicious food hubs.'; }

        $js_gems_db[$st]['gems'][$cat][] = [
            'name' => $attr['name'],
            'desc' => $attr['description'],
            'time' => $attr['recommended_time'],
            'maps' => $attr['maps_link'] ? $attr['maps_link'] : 'https://maps.google.com',
            'rating' => (string)$attr['rating'],
            'image' => $attr['image_url'],
            'reviews' => $attr['reviews_count']
        ];
    }
} catch (PDOException $e) {
    $js_gems_db = []; // Fallback
}

// 3. DAYS-DEPENDENT ITINERARY BOILERPLATE TEMPLATE IN PHP
$itinerary_templates = [
    1 => [ // 1 Day Template: 3 blocks
        ['day' => 1, 'time' => '09:00 AM', 'activity' => 'Morning visit to {place1}', 'budget' => 50],
        ['day' => 1, 'time' => '02:00 PM', 'activity' => 'Afternoon explorer at {place2}', 'budget' => 35],
        ['day' => 1, 'time' => '08:00 PM', 'activity' => 'Evening leisure at {place3}', 'budget' => 40]
    ],
    2 => [ // 2 Days Template: Unique Day 1 & Day 2 rows
        ['day' => 1, 'time' => '09:00 AM', 'activity' => 'Day 1 Morning: {place1}', 'budget' => 45],
        ['day' => 1, 'time' => '02:00 PM', 'activity' => 'Day 1 Afternoon: {place2}', 'budget' => 30],
        ['day' => 1, 'time' => '08:00 PM', 'activity' => 'Day 1 Night: {place3}', 'budget' => 40],
        ['day' => 2, 'time' => '09:30 AM', 'activity' => 'Day 2 Morning: {place4}', 'budget' => 55],
        ['day' => 2, 'time' => '03:00 PM', 'activity' => 'Day 2 Afternoon: {place5}', 'budget' => 45],
        ['day' => 2, 'time' => '07:30 PM', 'activity' => 'Day 2 Night: {place6}', 'budget' => 50]
    ],
    3 => [ // 3 Days or more template (Scalable loop block mapped in Javascript)
        ['day' => 1, 'time' => '09:00 AM', 'activity' => 'Day 1 Morning: {place1}', 'budget' => 50],
        ['day' => 1, 'time' => '02:00 PM', 'activity' => 'Day 1 Afternoon: {place2}', 'budget' => 30],
        ['day' => 1, 'time' => '08:00 PM', 'activity' => 'Day 1 Night: {place3}', 'budget' => 40],
        ['day' => 2, 'time' => '09:30 AM', 'activity' => 'Day 2 Morning: {place4}', 'budget' => 60],
        ['day' => 2, 'time' => '03:00 PM', 'activity' => 'Day 2 Afternoon: {place5}', 'budget' => 50],
        ['day' => 2, 'time' => '08:00 PM', 'activity' => 'Day 2 Night: {place6}', 'budget' => 45],
        ['day' => 3, 'time' => '10:00 AM', 'activity' => 'Day 3 Morning: {place7}', 'budget' => 55],
        ['day' => 3, 'time' => '01:30 PM', 'activity' => 'Day 3 Afternoon: {place8}', 'budget' => 40],
        ['day' => 3, 'time' => '07:00 PM', 'activity' => 'Day 3 Night: {place9}', 'budget' => 60]
    ]
];

// 4. AJAX DATE VALIDATION LAYER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_validate_dates'])) {
    header('Content-Type: application/json');
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    
    $today = date('Y-m-d');
    
    if (empty($start_date) || empty($end_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Please choose both travel start and end dates.']);
        exit;
    }
    
    if ($start_date < $today || $end_date < $today) {
        echo json_encode(['status' => 'error', 'message' => 'Travel dates cannot be in the past.']);
        exit;
    }
    
    if ($end_date < $start_date) {
        echo json_encode(['status' => 'error', 'message' => 'End date cannot be earlier than start date.']);
        exit;
    }
    
    echo json_encode(['status' => 'success']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> — Smart Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/smart-planner.css">
</head>
<body>

<?php include('bar.php'); ?>

<main>
    <div class="hero">
        <span class="hero-tag">Smart Planner</span>
        <h1>Explore all <span>Hidden Places</span> and Things To Do in Malaysia</h1>
    </div>

   
     
        
   
    <div class="layout">

        <aside class="sidebar">
            <div class="filter-section search-wrapper" style="position: relative; margin-bottom: 22px;">
                <div class="sidebar-label">Search Hidden Gems</div>
                <div class="search-input-container" style="position: relative;">
                    <i class="ri-search-line" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4); font-size: 0.9rem;"></i>
                    <input class="sidebar-input" type="text" id="gem-search-input" placeholder="e.g., Pulau Kapas, Batu Caves..." style="padding-left: 36px;" autocomplete="off">
                </div>
                
                <div id="search-recommendations" class="recommendations-dropdown" style="display: none;"></div>

                <div class="popular-searches" style="margin-top: 10px;">
                    <div class="date-sublabel" style="margin-bottom: 6px;">Popular Searches</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span class="pop-tag" onclick="quickSearch('Pulau Kapas')">Pulau Kapas</span>
                        <span class="pop-tag" onclick="quickSearch('Batu Caves')">Batu Caves</span>
                        <span class="pop-tag" onclick="quickSearch('Mossy Forest')">Mossy Forest</span>
                    </div>
                </div>
            </div>
            <div class="sidebar-label">Where are you traveling?</div>
            <div class="date-container">
                <div>
                    <div class="date-sublabel">Start Date</div>
                    <input class="sidebar-input" type="date" id="start-date">
                </div>
                <div>
                    <div class="date-sublabel">End Date</div>
                    <input class="sidebar-input" type="date" id="end-date">
                </div>
            </div>

            <div class="filter-section">
                <div class="filter-title">State</div>
                <div class="filter-check" id="state-filters">
                    <?php
                    $states = ["Terengganu","Perak","Selangor","Pulau Pinang","Pahang","Johor","Sabah","Sarawak","Kedah","Kelantan"];
                    foreach ($states as $state):
                    ?>
                    <label>
                        <input type="checkbox" class="state-checkbox" value="<?= htmlspecialchars($state) ?>"> <?= htmlspecialchars($state) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            

           

            <button class="next-btn" id="next-step-btn">Next Step &nbsp;<i class="ri-arrow-right-line"></i></button>
        </aside>

        <section class="cards-list">
            <!-- Dynamic category filtering tabs -->
            <div class="category-tabs-container" id="category-tabs-row">
                <button class="category-tab active" data-category="All"><i class="ri-compass-3-line"></i> All Gems</button>
                <button class="category-tab" data-category="Beach"><i class="ri-umbrella-line"></i> Beaches</button>
                <button class="category-tab" data-category="Nature"><i class="ri-leaf-line"></i> Nature</button>
                <button class="category-tab" data-category="Adventure"><i class="ri-riding-line"></i> Adventure</button>
                <button class="category-tab" data-category="Culture"><i class="ri-ancient-gate-line"></i> Culture</button>
                <button class="category-tab" data-category="Food"><i class="ri-restaurant-2-line"></i> Food & Dining</button>
            </div>

            <!-- Smart Itinerary Panel -->
            <div id="itinerary-section" class="itinerary-section" style="display: none;"></div>

            <!-- Attractions Place Grid -->
            <div class="places-grid" id="planner-cards-container">
                <div class="empty-state">
                    <i class="ri-map-pin-time-line"></i>
                    <h3>Start Planning Your Journey</h3>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-top: 8px;">
                        Select a travel date, state and travel preferences on the sidebar to instantly generate your smart itinerary.
                    </p>
                </div>
            </div>
        </section>

    </div>
</main>

<div class="toast" id="toast-notif">
    <i class="ri-checkbox-circle-fill"></i>
    <span>Trip Planner Saved Successfully!</span>
</div>

<script>
    // Injected models
    const hiddenGemsDatabase = <?= json_encode($js_gems_db) ?>;
    const itineraryTemplates = <?= json_encode($itinerary_templates) ?>;
</script>
<script src="js/smart-planner.js"></script>
</body>
</html>
