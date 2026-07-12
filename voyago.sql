-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2026 at 02:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `voyago`
--

-- --------------------------------------------------------

--
-- Table structure for table `attractions`
--

CREATE TABLE `attractions` (
  `id` int(11) NOT NULL,
  `state` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `recommended_time` varchar(50) NOT NULL,
  `maps_link` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT 4.5,
  `reviews_count` varchar(50) DEFAULT '100 reviews'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attractions`
--

INSERT INTO `attractions` (`id`, `state`, `category`, `name`, `description`, `recommended_time`, `maps_link`, `image_url`, `rating`, `reviews_count`) VALUES
(1, 'Terengganu', 'Beach', 'Pulau Gemia', 'Private island paradise offering unparalleled seclusion and exquisite marine ecosystems.', '4-6 Hours', 'https://maps.google.com', 'gemia.png', 4.8, '1,240 reviews'),
(2, 'Terengganu', 'Beach', 'Pantai Kelulut', 'Serene stretch of sandy beach flanked by local traditional seafood pavilions.', '1-2 Hours', 'https://maps.google.com', 'kelulut.jpg', 4.5, '540 reviews'),
(3, 'Terengganu', 'Beach', 'Pantai Kemasik', 'Famous for its unique structural rock formations rising majestically out of the lagoon.', '2-3 Hours', 'https://maps.google.com', 'kemasik.jpg', 4.6, '720 reviews'),
(4, 'Terengganu', 'Beach', 'Pulau Kapas', 'Beautiful hidden island with crystal clear emerald waters and fewer tourist crowds.', '5-8 Hours', 'https://maps.google.com', 'kapas.jpg', 4.8, '1,240 reviews'),
(5, 'Terengganu', 'Nature', 'Sekayu Waterfall', 'Multi-tiered cascades set inside a lush, tranquil forest reserve with natural pools.', '3-4 Hours', 'https://maps.google.com', 'sekayu.jpg', 4.5, '820 reviews'),
(6, 'Terengganu', 'Nature', 'Bukit Keluang', 'Offers dynamic, panoramic coastal vistas where the jungle meets the sea cliff edges.', '2-3 Hours', 'https://maps.google.com', 'keluang.jpg', 4.7, '1,100 reviews'),
(7, 'Terengganu', 'Nature', 'Tasik Kenyir', 'Southeast Asia\'s largest artificial lake, rich with secret coves and floating sanctuaries.', '1-2 Days', 'https://maps.google.com', 'kenyir.jpg', 4.6, '930 reviews'),
(8, 'Terengganu', 'Nature', 'Lata Belatan', 'Enchanting forest eco-park serving as the main gateway to Mount Tebu.', '2-4 Hours', 'https://maps.google.com', 'belatan.jpg', 4.4, '310 reviews'),
(9, 'Terengganu', 'Adventure', 'ATV Pantai Penarik', 'Thrilling all-terrain vehicle ride cruising right across the long shoreline.', '1-2 Hours', 'https://maps.google.com', 'penarik_atv.jpg', 4.6, '240 reviews'),
(10, 'Terengganu', 'Adventure', 'Kayaking Kenyir', 'Paddle through historic submerged gorges and pristine ancient rainforest canopies.', '3-5 Hours', 'https://maps.google.com', 'kenyir_kayak.jpg', 4.7, '180 reviews'),
(11, 'Terengganu', 'Adventure', 'Island Hopping Marang', 'High-speed boat venture to isolated sandbars and secret marine sanctuaries.', '4-6 Hours', 'https://maps.google.com', 'marang.jpg', 4.8, '670 reviews'),
(12, 'Terengganu', 'Adventure', 'Hiking Bukit Besi', 'Challenging jungle trekking exploring historical remains of regional iron mining.', '3-4 Hours', 'https://maps.google.com', 'bukitbesi.jpg', 4.3, '120 reviews'),
(13, 'Terengganu', 'Culture', 'Chinatown KT', 'Vibrant historic enclave with heritage shophouses, street murals, and old clan houses.', '2-3 Hours', 'https://maps.google.com', 'chinatown_kt.jpg', 4.5, '850 reviews'),
(14, 'Terengganu', 'Culture', 'Pasar Payang', 'Centuries-old market center rich with authentic local batik, crafts, and traditional snacks.', '2-3 Hours', 'https://maps.google.com', 'pasarpayang.jpg', 4.4, '1,450 reviews'),
(15, 'Terengganu', 'Culture', 'Kampung Losong', 'The legendary heritage village home to authentic traditional fish cracker (Keropok Lekor) artisans.', '1 Hour', 'https://maps.google.com', 'losong.jpg', 4.6, '680 reviews'),
(16, 'Terengganu', 'Culture', 'Terrapuri Heritage Village', 'Conservation project showcasing 29 classic, majestic antique Malay palaces.', '2-3 Hours', 'https://maps.google.com', 'terrapuri.png', 4.8, '1,240 reviews'),
(17, 'Terengganu', 'Food', 'Nasi Dagang Atas Tol', 'Authentic local breakfast spot famous for soft, aromatic Nasi Dagang served with tuna curry.', '1-2 Hours', 'https://maps.google.com', 'nasidagang.jpg', 4.9, '2,100 reviews'),
(18, 'Terengganu', 'Food', 'Warung Syukur Keropok Lekor', 'Savor crispy, fresh keropok lekor fried hot on the spot along the coastal road.', '1 Hour', 'https://maps.google.com', 'keropok_lekor.jpg', 4.8, '1,320 reviews'),
(19, 'Perak', 'Beach', 'Coral Beach Pangkor', 'Secluded pristine white sand strip perfect for quiet sunsets and clean waters.', '2-4 Hours', 'https://maps.google.com', 'coralbeach.png', 4.7, '890 reviews'),
(20, 'Perak', 'Beach', 'Teluk Batik', 'Lovely dynamic bay bordered by sweeping coconut trees and beachfront markets.', '2-3 Hours', 'https://maps.google.com', 'telukbatik.jpg', 4.3, '1,200 reviews'),
(21, 'Perak', 'Beach', 'Teluk Nipah', 'Energetic coastal village slice offering local watersports and stunning view.', '3-5 Hours', 'https://maps.google.com', 'teluknipah.jpg', 4.5, '1,500 reviews'),
(22, 'Perak', 'Beach', 'Pulau Giam', 'Tiny islet reachable by foot during low tides, hosting vibrant shallow corals.', '2-3 Hours', 'https://maps.google.com', 'pulaugiam.jpg', 4.6, '410 reviews'),
(23, 'Perak', 'Nature', 'Kuala Sepetang Mangrove', 'Malaysia\'s best-managed mangrove ecosystem, filled with wildlife and bird species.', '3-4 Hours', 'https://maps.google.com', 'sepetang.jpg', 4.5, '620 reviews'),
(24, 'Perak', 'Nature', 'Kek Lok Tong', 'Breathtaking cavern system houses detailed Buddhist altars inside massive limestone spaces.', '1-2 Hours', 'https://maps.google.com', 'kekloktong.jpg', 4.7, '1,800 reviews'),
(25, 'Perak', 'Nature', 'Royal Belum Rainforest', 'An ancient forest reserve older than the Amazon, housing rare hornbills and elephants.', '1-2 Days', 'https://maps.google.com', 'royalbelum.jpg', 4.8, '560 reviews'),
(26, 'Perak', 'Nature', 'Gua Tempurung', 'One of the longest and most spectacular natural cave networks across Peninsula Malaysia.', '3-4 Hours', 'https://maps.google.com', 'guatempurung.jpg', 4.6, '1,240 reviews'),
(27, 'Perak', 'Adventure', 'White Water Rafting Gopeng', 'High-octane adrenaline rush down class I-IV rapids of the scenic Kampar River.', '3-4 Hours', 'https://maps.google.com', 'gopeng_rafting.jpg', 4.8, '450 reviews'),
(28, 'Perak', 'Adventure', 'Hiking Bukit Larut', 'Trek up Maxwell Hill amidst cool misty climates and historical bungalow ruins.', '4-6 Hours', 'https://maps.google.com', 'bukitlarut.jpg', 4.5, '310 reviews'),
(29, 'Perak', 'Adventure', 'Water Tubing Kampar', 'Fun, relaxing floating downstream along picturesque river rapids wrapped in greenery.', '2 Hours', 'https://maps.google.com', 'kampar_tubing.jpg', 4.6, '220 reviews'),
(30, 'Perak', 'Adventure', 'Caving Adventure Gua Kandu', 'Demanding deep cave exploration mapping dark chambers and tight rock crawl spaces.', '3-5 Hours', 'https://maps.google.com', 'guakandu.jpg', 4.5, '180 reviews'),
(31, 'Perak', 'Culture', 'Ipoh Old Town Heritage Walk', 'Discover colonial landmarks, Concubine Lane alleyways, and old wall art gems.', '2-4 Hours', 'https://maps.google.com', 'ipoh_oldtown.jpg', 4.6, '2,400 reviews'),
(32, 'Perak', 'Culture', 'Kellie\'s Castle', 'Unfinished mystical Scottish mansion rich with tragic romance stories and hidden passages.', '2 Hours', 'https://maps.google.com', 'kellies_castle.jpg', 4.5, '1,980 reviews'),
(33, 'Perak', 'Culture', 'Kuala Kangsar Royal District', 'Admire beautiful classic palaces and the golden architecture of Ubudiah Mosque.', '2-3 Hours', 'https://maps.google.com', 'kualakangsar.jpg', 4.6, '450 reviews'),
(34, 'Perak', 'Culture', 'Matang Mangrove Charcoal Factory', 'Century-old smokehouse processing logs using heritage traditional burning styles.', '1 Hour', 'https://maps.google.com', 'charcoal_factory.jpg', 4.5, '320 reviews'),
(35, 'Perak', 'Food', 'Ipoh White Coffee & Dim Sum', 'Sample authentic locally brewed white coffee and delicious traditional handmade dim sum.', '1-2 Hours', 'https://maps.google.com', 'ipoh_coffee.jpg', 4.8, '3,100 reviews'),
(36, 'Perak', 'Food', 'Nasi Ganja Yong Suan', 'Traditional legendary spicy rice mixed with fragrant red curries and crispy chicken.', '1 Hour', 'https://maps.google.com', 'nasiganja.jpg', 4.9, '2,800 reviews'),
(37, 'Selangor', 'Beach', 'Redang Beach Sekinchan', 'Unique coastal beach strip featuring old wishing trees and seaside wooden shacks.', '1-2 Hours', 'https://maps.google.com', 'redang_beach.jpg', 4.2, '910 reviews'),
(38, 'Selangor', 'Beach', 'Pantai Morib', 'Historical, nostalgic beach park perfect for breezy evening strolls and kite flying.', '2 Hours', 'https://maps.google.com', 'morib.jpg', 4.0, '1,500 reviews'),
(39, 'Selangor', 'Beach', 'Pantai Remis', 'Rocky shoreline offering beautiful sea views and dynamic sunset seafood setups.', '1-2 Hours', 'https://maps.google.com', 'morib.jpg', 4.1, '820 reviews'),
(40, 'Selangor', 'Beach', 'Pulau Ketam', 'Floating wooden fishing village built entirely over coastal mangrove mudflats.', '4-6 Hours', 'https://maps.google.com', 'pulau_ketam.jpg', 4.4, '1,100 reviews'),
(41, 'Selangor', 'Nature', 'Kampung Kuantan Fireflies', 'Magical night boat tour through mangrove rivers illuminated by thousands of synchronized fireflies.', '1-2 Hours', 'https://maps.google.com', 'fireflies.jpg', 4.7, '1,650 reviews'),
(42, 'Selangor', 'Nature', 'Kuala Selangor Nature Park', 'Expansive dynamic wetland forest rich with mudskippers, crabs, and silvered leaf monkeys.', '2-3 Hours', 'https://maps.google.com', 'ks_naturepark.jpg', 4.3, '780 reviews'),
(43, 'Selangor', 'Nature', 'Templer Park Rainforest', 'Serene jungle park with natural cascades and cold pools under towering limestone views.', '3-4 Hours', 'https://maps.google.com', 'templer_park.jpg', 4.4, '690 reviews'),
(44, 'Selangor', 'Nature', 'Sky Mirror Sasaran', 'Amazing sandbar phenomenon reflecting skies like a giant mirror during low tides.', '3-4 Hours', 'https://maps.google.com', 'skymirror.png', 4.7, '2,105 reviews'),
(45, 'Selangor', 'Adventure', 'Skytrex Shah Alam', 'Thrilling high-rope obstacle course suspended high within rainforest canopies.', '3 Hours', 'https://maps.google.com', 'skytrex.jpeg', 4.6, '540 reviews'),
(46, 'Selangor', 'Adventure', 'Paragliding Jugra Hill', 'Unforgettable tandem flight soaring high over historic hills and river deltas.', '1-2 Hours', 'https://maps.google.com', 'jugra_paragliding.jpg', 4.7, '280 reviews'),
(47, 'Selangor', 'Adventure', 'Hiking Bukit Broga', 'Famous ridge hike offering spectacular sunrises over rolling fields of lalang grass.', '2-3 Hours', 'https://maps.google.com', 'broga_hill.jpg', 4.5, '1,890 reviews'),
(48, 'Selangor', 'Adventure', 'White Water Rafting Ulu Selangor', 'Exciting, hidden river rafting route providing adrenaline hits amidst untouched wilderness.', '4 Hours', 'https://maps.google.com', 'uluselangor_rafting.jpg', 4.6, '150 reviews'),
(49, 'Selangor', 'Culture', 'Batu Caves', 'Iconic steep rainbow stairways leading up into deep, ancient limestone temple caverns.', '2-3 Hours', 'https://maps.google.com', 'batucaves.jpg', 4.6, '5,400 reviews'),
(50, 'Selangor', 'Culture', 'Sultan Salahuddin Abdul Aziz Mosque', 'Magnificent Blue Mosque boasting one of the world\'s largest religious domes.', '1-2 Hours', 'https://maps.google.com', 'bluemosque.jpg', 4.8, '2,300 reviews'),
(51, 'Selangor', 'Culture', 'Mah Meri Cultural Village', 'Indigenous heritage community legendary for highly intricate, expressive woodcarvings.', '2-3 Hours', 'https://maps.google.com', 'mahmeri.jpg', 4.5, '310 reviews'),
(52, 'Selangor', 'Culture', 'Klang Royal Heritage Walk', 'Explore classic architectures detailing the rich royal history of Selangor.', '2-3 Hours', 'https://maps.google.com', 'klang_walk.jpg', 4.3, '420 reviews'),
(53, 'Selangor', 'Food', 'Kajang Satay Haji Samuri', 'Sample authentic wood-coal grilled satay skewers served with spicy peanut sauce.', '1-2 Hours', 'https://maps.google.com', 'satay_kajang.jpg', 4.7, '3,800 reviews'),
(54, 'Selangor', 'Food', 'Klang Bak Kut Teh', 'Traditional rich, aromatic herbal meat broth that originated in the port city of Klang.', '1-2 Hours', 'https://maps.google.com', 'klang_bkt.jpg', 4.6, '2,900 reviews'),
(55, 'Pulau Pinang', 'Beach', 'Monkey Beach', 'Secluded sandy strip inside Penang National Park accessible only by boat or trek.', '3-5 Hours', 'https://maps.google.com', 'monkey_beach.jpg', 4.3, '980 reviews'),
(56, 'Pulau Pinang', 'Beach', 'Kerachut Beach', 'Untouched coastline hosting a rare meromictic lake and sea turtle hatchery.', '4-6 Hours', 'https://maps.google.com', 'kerachut.jpg', 4.6, '640 reviews'),
(57, 'Pulau Pinang', 'Beach', 'Batu Ferringhi', 'Vibrant coastal stretch offering active watersports and dynamic night bazaars.', '2-4 Hours', 'https://maps.google.com', 'batu_ferringhi.jpg', 4.4, '3,100 reviews'),
(58, 'Pulau Pinang', 'Beach', 'Teluk Kampi', 'The longest, most isolated deep beach zone inside the park limits, highly private.', '5-6 Hours', 'https://maps.google.com', 'telukkampi.jpg', 4.5, '220 reviews'),
(59, 'Pulau Pinang', 'Nature', 'The Habitat Penang Hill', 'World-class eco-tourism rainforest park with an architectural 360-degree canopy walkway.', '3-4 Hours', 'https://maps.google.com', 'thehabitat.jpg', 4.8, '2,400 reviews'),
(60, 'Pulau Pinang', 'Nature', 'Entopia Butterfly Farm', 'Massive indoor live paradise dome housing thousands of free-flying tropical butterflies.', '2-3 Hours', 'https://maps.google.com', 'entopia.jpg', 4.7, '1,950 reviews'),
(61, 'Pulau Pinang', 'Nature', 'Penang Botanic Gardens', 'Historic verdant landscape park set below high hills, known for its wild monkeys.', '1-2 Hours', 'https://maps.google.com', 'penang_botanic.jpg', 4.3, '1,100 reviews'),
(62, 'Pulau Pinang', 'Nature', 'Frog Hill Tasek Gelugor', 'Abandoned quarry site containing stunning, vibrant red clay landscapes and emerald pools.', '1-2 Hours', 'https://maps.google.com', 'froghill.jpg', 4.4, '540 reviews'),
(63, 'Pulau Pinang', 'Adventure', 'Escape Theme Park', 'Home to the world\'s longest jungle water slide and extreme ropes courses.', '5-8 Hours', 'https://maps.google.com', 'escapethemepark.png', 4.9, '3,450 reviews'),
(64, 'Pulau Pinang', 'Adventure', 'Hiking to Penang Hill via Heritage Trail', 'Demanding uphill hike following the route of the historic funicular train track.', '3-4 Hours', 'https://maps.google.com', 'penanghill_hike.jpg', 4.6, '890 reviews'),
(65, 'Pulau Pinang', 'Adventure', 'ATV Balik Pulau', 'Ride through scenic countryside, local paddy fields, and traditional mangrove margins.', '1-2 Hours', 'https://maps.google.com', 'balikpulau_atv.jpg', 4.7, '230 reviews'),
(66, 'Pulau Pinang', 'Adventure', 'Jet Skiing Batu Ferringhi', 'High-speed water adventure tracking the expansive coastline of Penang.', '1 Hour', 'https://maps.google.com', 'jetski.jpg', 4.3, '310 reviews'),
(67, 'Pulau Pinang', 'Culture', 'George Town Heritage Murals', 'Hunting world-famous interactive street art pieces curated by Ernest Zacharevic.', '2-4 Hours', 'https://maps.google.com', 'penang_murals.jpg', 4.7, '4,200 reviews'),
(68, 'Pulau Pinang', 'Culture', 'Kek Lok Si Temple', 'Grandest Buddhist temple complex in Southeast Asia, with a giant bronze Guanyin statue.', '2-3 Hours', 'https://maps.google.com', 'kekloksi.jpg', 4.7, '3,200 reviews'),
(69, 'Pulau Pinang', 'Culture', 'Cheong Fatt Tze Blue Mansion', 'Award-winning indigo-blue heritage Chinese courtyard home of a prominent historian.', '1-2 Hours', 'https://maps.google.com', 'bluemansion.jpg', 4.6, '1,500 reviews'),
(70, 'Pulau Pinang', 'Culture', 'Clan Jetties', 'Historic 19th-century Chinese waterfront settlements built fully on high wooden stilts.', '1-2 Hours', 'https://maps.google.com', 'clanjetties.jpg', 4.4, '1,800 reviews'),
(71, 'Pulau Pinang', 'Food', 'George Town Street Food Trail', 'Famous local hawker food tour including Char Kway Teow, Penang Laksa, and Cendol.', '1-2 Hours', 'https://maps.google.com', 'penang_food.jpg', 4.9, '5,200 reviews'),
(72, 'Pulau Pinang', 'Food', 'Line Clear Nasi Kandar', 'Historical legendary local Indian-Muslim restaurant offering robust curry assortments.', '1 Hour', 'https://maps.google.com', 'nasikandar.jpg', 4.8, '4,100 reviews'),
(73, 'Pahang', 'Beach', 'Juara Beach Tioman', 'Quiet, peaceful golden bay on the eastern flank of Tioman Island with clean streams.', '4-8 Hours', 'https://maps.google.com', 'juara.jpg', 4.7, '450 reviews'),
(74, 'Pahang', 'Beach', 'Pantai Teluk Cempedak', 'Iconic white sand bay offering raised wooden walkways winding around rocky headlands.', '2-3 Hours', 'https://maps.google.com', 'cempedak.jpg', 4.4, '2,100 reviews'),
(75, 'Pahang', 'Beach', 'Pantai Cherating', 'Famed cultural surf beach spot known for its laidback village soul and turtle sanctuaries.', '3-5 Hours', 'https://maps.google.com', 'cherating.jpg', 4.5, '1,300 reviews'),
(76, 'Pahang', 'Beach', 'Monkey Bay Tioman', 'Stunning hidden desert oasis shaped like an hourglass, exceptional for snorkeling.', '3-4 Hours', 'https://maps.google.com', 'mokey_bay.jpg', 4.6, '320 reviews'),
(77, 'Pahang', 'Nature', 'Taman Negara Canopy Walk', 'Walk on the world\'s longest canopy bridge system built deep within ancient rainforests.', '3-5 Hours', 'https://maps.google.com', 'tamannegara.jpg', 4.6, '1,450 reviews'),
(78, 'Pahang', 'Nature', 'Mossy Forest Brinchang', 'Surreal cloud forest landscape covered entirely in thick green moss, mist, and lichens.', '2 Hours', 'https://maps.google.com', 'mossyforest.jpg', 4.7, '1,678 reviews'),
(79, 'Pahang', 'Nature', 'Sungai Chiling Waterfall', 'Scenic river trail requiring multiple stream crossings leading to an incredible river pool.', '4-5 Hours', 'https://maps.google.com', 'chiling.jpg', 4.6, '810 reviews'),
(80, 'Pahang', 'Nature', 'Tasik Chini', 'Mystical natural freshwater lake rich with summer lotus blooms.', '3-4 Hours', 'https://maps.google.com', 'chini.jpg', 4.1, '340 reviews'),
(81, 'Pahang', 'Adventure', 'Cameron Highlands Mossy Trekking', 'Rugged hiking up slippery muddy slopes mapping high mistry ridge trails.', '3-5 Hours', 'https://maps.google.com', 'cameron_trek.jpg', 4.5, '620 reviews'),
(82, 'Pahang', 'Adventure', 'Rapid Shooting Taman Negara', 'Thrilling wooden boat ride negotiating swirling, splashing white-water river rapids.', '2 Hours', 'https://maps.google.com', 'rapid_shooting.jpg', 4.6, '410 reviews'),
(83, 'Pahang', 'Adventure', 'Scuba Diving Tioman', 'Explore deep marine parks filled with sea turtles, reef sharks, and coral gardens.', '4-6 Hours', 'https://maps.google.com', 'tioman_dive.jpg', 4.8, '1,100 reviews'),
(84, 'Pahang', 'Adventure', 'Hiking Bukit Panorama', 'Early morning hill climb to capture an ocean of morning clouds over historic towns.', '2 Hours', 'https://maps.google.com', 'panorama_hill.jpg', 4.7, '530 reviews'),
(85, 'Pahang', 'Culture', 'Sungai Palas Tea Garden', 'Stunning futuristic cafe cantilevered over endless rolling hills of tea plantations.', '2-3 Hours', 'https://maps.google.com', 'sungaipalas.jpg', 4.7, '2,800 reviews'),
(86, 'Pahang', 'Culture', 'Kuala Gandah Elephant Sanctuary', 'Ecotourism center focused on rescuing, rehabilitating, and protecting Asian elephants.', '3-4 Hours', 'https://maps.google.com', 'elephants.jpg', 4.5, '1,200 reviews'),
(87, 'Pahang', 'Culture', 'Sungai Lembing Underground Mines', 'Step inside what was once the largest and deepest underground tin mine in the world.', '2 Hours', 'https://maps.google.com', 'lembing_mines.jpg', 4.4, '650 reviews'),
(88, 'Pahang', 'Culture', 'Raub Old Town Traditional Street', 'Quaint colonial heritage streets filled with historic traditional coffee shops.', '1-2 Hours', 'https://maps.google.com', 'raub_town.jpg', 4.3, '180 reviews'),
(89, 'Pahang', 'Food', 'Bentong Ginger & Durian Trail', 'Enjoy authentic Bentong ginger dishes, hand-churned ice cream, and fresh Musang King durians.', '1-2 Hours', 'https://maps.google.com', 'bentong_food.jpg', 4.8, '1,500 reviews'),
(90, 'Pahang', 'Food', 'Cameron Highland Strawberry Waffles', 'Fresh local strawberries paired with warm waffles and tea at Boh Plantation farms.', '1-2 Hours', 'https://maps.google.com', 'strawberry_waffles.jpg', 4.7, '2,100 reviews'),
(91, 'Johor', 'Beach', 'Rawa Island (Pulau Mawar)', 'Private island paradise offering unparalleled seclusion and beautiful crystal-clear coral horizons.', '5-8 Hours', 'https://maps.google.com', 'pulaumawar.png', 4.7, '1,520 reviews'),
(92, 'Johor', 'Beach', 'Desaru Coast Beach', 'Wide premium shoreline featuring premium family resorts and clear coastlines.', '3-4 Hours', 'https://maps.google.com', 'desaru.jpeg', 4.5, '1,980 reviews'),
(93, 'Johor', 'Beach', 'Sibu Island (Pulau Sibu)', 'Quiet tropical escape ideal for spotting golden sand lines and shallow coral diving.', '4-6 Hours', 'https://maps.google.com', 'sibu.jpg', 4.4, '840 reviews'),
(94, 'Johor', 'Beach', 'Pantai Minyak Beku', 'Historical beach site offering serene sunset strolls and a unique local monument backstory.', '1-2 Hours', 'https://maps.google.com', 'minyakbeku.jpeg', 4.2, '540 reviews'),
(95, 'Johor', 'Nature', 'Endau-Rompin National Park', 'Explore one of the oldest ancient tropical rainforest networks inside Peninsular Malaysia.', '1-2 Days', 'https://maps.google.com', 'endau_rompin.jpeg', 4.8, '820 reviews'),
(96, 'Johor', 'Nature', 'Gunung Pulai Recreational Forest', 'Popular local mountain trek offering refreshing natural streams and multi-tiered waterfall pools.', '3-4 Hours', 'https://maps.google.com', 'gunung_pulai.jpg', 4.4, '940 reviews'),
(97, 'Johor', 'Nature', 'Tanjung Piai Mangrove Park', 'Walk down wooden paths traversing thick coastal swamp forests to visit the southernmost point of Mainland Asia.', '2-3 Hours', 'https://maps.google.com', 'tanjung_piai.jpg', 4.5, '1,120 reviews'),
(98, 'Johor', 'Nature', 'Kota Tinggi Firefly Park', 'Magical evening river cruise tracking thousands of blinking fireflies reflecting on river canals.', '1-2 Hours', 'https://maps.google.com', 'kotatinggi_fireflies.jpg', 4.6, '720 reviews'),
(99, 'Johor', 'Adventure', 'LegoLand Malaysia Theme Park', 'High-octane amusement rides, immersive waterpark slides, and complex block architecture models.', '5-8 Hours', 'https://maps.google.com', 'legoland.jpeg', 4.8, '3,800 reviews'),
(100, 'Johor', 'Adventure', 'Austin Heights Water Adventure', 'Thrilling high-rope obstacle courses, zip-lines, and large water-fun drop points.', '4-5 Hours', 'https://maps.google.com', 'austin_heights.jpeg', 4.5, '1,020 reviews'),
(101, 'Johor', 'Adventure', 'Hiking Bukit Selantai', 'Challenging hill scramble providing a reward of breathtaking panoramic sea views from the peak line.', '2-3 Hours', 'https://maps.google.com', 'selantai.jpeg', 4.4, '310 reviews'),
(102, 'Johor', 'Adventure', 'ATV Park Johor Bahru', 'Get muddy riding powerful off-road vehicles through deep jungle trails and rugged tracks.', '1-2 Hours', 'https://maps.google.com', 'johor_atv.jpg', 4.6, '450 reviews'),
(103, 'Johor', 'Culture', 'Johor Bahru City Centre (Heritage Walk)', 'Tour old colonial lanes, classic shophouses, and ancient temples tucked inside the city limits.', '2-3 Hours', 'https://maps.google.com', 'default_place.jpg', 4.5, '1,150 reviews'),
(104, 'Johor', 'Culture', 'Muar Historical Town', 'Renowned royal town iconic for classic pre-war layout arts and famous local coffee houses.', '3-4 Hours', 'https://maps.google.com', 'muar_town.jpeg', 4.6, '890 reviews'),
(105, 'Johor', 'Culture', 'Sultan Abu Bakar State Mosque', 'Stunning 19th-century architecture blending Victorian aesthetics with classic Moorish designs.', '1 Hour', 'https://maps.google.com', 'sultan_mosque.jpeg', 4.8, '1,650 reviews'),
(106, 'Johor', 'Culture', 'Tan Hiok Nee Heritage Street', 'Vibrant cultural hub filled with traditional bakeries, murals, and old Chinese clan spaces.', '1-2 Hours', 'https://maps.google.com', 'voyagologo.png', 4.7, '1,980 reviews'),
(107, 'Johor', 'Food', 'Muar Mee Bandung & Otak-Otak', 'Sample Muar\'s signature thick egg noodle broth and savory charcoal-grilled fish paste.', '1 Hour', 'https://maps.google.com', 'muar_food.jpeg', 4.8, '1,420 reviews'),
(108, 'Johor', 'Food', 'Larkin Kacang Pool Haji', 'Traditional Middle Eastern inspired local bean paste stew topped with sunny side egg and toast.', '1 Hour', 'https://maps.google.com', 'kacang_pool.jpeg', 4.7, '1,200 reviews'),
(109, 'Sabah', 'Beach', 'Sipadan Island Marine Park', 'Globally celebrated diving destination hosting thousands of sea turtles and swirling barracuda walls.', '5-8 Hours', 'https://maps.google.com', 'sipadan.png', 4.9, '2,410 reviews'),
(110, 'Sabah', 'Beach', 'Mabul Island Vista', 'Incredible white sand sandbars looking down directly into premium shallow coral gardens.', '4-6 Hours', 'https://maps.google.com', 'mabul.jpeg', 4.7, '1,800 reviews'),
(111, 'Sabah', 'Beach', 'Tanjung Aru Beach', 'Famous sunset coastal strip providing beautiful orange horizons alongside local food centers.', '2 Hours', 'https://maps.google.com', 'tanjung_aru.jpeg', 4.5, '3,100 reviews'),
(112, 'Sabah', 'Beach', 'Mantanani Islands', 'Isolated clear water islands perfect for peaceful swimming and spotting rare marine life.', '5-8 Hours', 'https://maps.google.com', 'mantanani.jpg', 4.6, '740 reviews'),
(113, 'Sabah', 'Nature', 'Mount Kinabalu & Kundasang Highlands', 'Breathtaking cool valleys set against the backdrop of Malaysia\'s highest majestic peak.', '1-2 Days', 'https://maps.google.com', 'kundasang.png', 4.9, '5,200 reviews'),
(114, 'Sabah', 'Nature', 'Sepilok Orangutan Sanctuary', 'Observe orphaned wild orangutans learning survival steps on specialized forest platforms.', '2-3 Hours', 'https://maps.google.com', 'sepilok.jpeg', 4.7, '2,600 reviews'),
(115, 'Sabah', 'Nature', 'Danum Valley Conservation Area', 'Untouched ancient primary rainforest ecosystem tracking rare pygmy elephants and leopards.', '1-2 Days', 'https://maps.google.com', 'danum_valley.jpeg', 4.8, '540 reviews'),
(116, 'Sabah', 'Nature', 'Kinabatangan River Cruise', 'Relaxing boat expedition searching for proboscis monkeys and crocodiles along muddy riverbanks.', '3-4 Hours', 'https://maps.google.com', 'kinabatangan.jpeg', 4.6, '1,420 reviews'),
(117, 'Sabah', 'Adventure', 'Kiulu River White Water Rafting', 'Gentne yet exciting river rapid rafting route ideal for capturing scenic valley views.', '3 Hours', 'https://maps.google.com', 'kiulu.jpeg', 4.5, '980 reviews'),
(118, 'Sabah', 'Adventure', 'Padas River Extreme Rafting', 'High-adrenaline white-water route negotiating rough class III-IV rapids inside deep gorges.', '4-5 Hours', 'https://maps.google.com', 'padas.jpeg', 4.6, '420 reviews'),
(119, 'Sabah', 'Adventure', 'Via Ferrata Kinabalu', 'The world\'s highest alpine mountain rock walkway route utilizing specialized secure wire lines.', '5-8 Hours', 'https://maps.google.com', 'via_ferrata.jpeg', 4.8, '630 reviews'),
(120, 'Sabah', 'Adventure', 'Hiking Maragang Hill', 'Rewarding early-morning hike to witness Kinabalu\'s stone peaks emerging above low clouds.', '3-4 Hours', 'https://maps.google.com', 'maragang.jpeg', 4.7, '340 reviews'),
(121, 'Sabah', 'Culture', 'Gaya Street Sunday Market', 'Bustling historical market displaying unique local items, handicrafts, and Bornean snacks.', '2-3 Hours', 'https://maps.google.com', 'gayastreet.jpg', 4.5, '2,400 reviews'),
(122, 'Sabah', 'Culture', 'Mari Mari Cultural Village', 'Interactive tribal settlement showcasing traditional houses, fire-starting, and blowpipe skills.', '3-4 Hours', 'https://maps.google.com', 'marimari.jpeg', 4.8, '1,890 reviews'),
(123, 'Sabah', 'Culture', 'Monsopiad Heritage Village', 'Step inside a historical site chronicling the legendary stories of Kadazan headhunters.', '2 Hours', 'https://maps.google.com', 'monsopiad.jpeg', 4.4, '310 reviews'),
(124, 'Sabah', 'Culture', 'Sabah State Museum & Heritage Village', 'Expansive archives documenting ancestral archaeological pottery and native longhouses.', '2 Hours', 'https://maps.google.com', 'sabah_museum.jpg', 4.3, '780 reviews'),
(125, 'Sabah', 'Food', 'Tuaran Mee & Sabah Seafood', 'Taste classic egg noodles fried with pork char siew and fresh lobster/shrimp platters.', '1-2 Hours', 'https://maps.google.com', 'tuaran_mee.jpeg', 4.8, '1,680 reviews'),
(126, 'Sabah', 'Food', 'Beaufort Nasi Penyet & Tenom Coffee', 'Sample crispy deep-fried chicken paired with famous aromatic wood-roasted Tenom coffee.', '1 Hour', 'https://maps.google.com', 'tenom_coffee.jpeg', 4.7, '1,100 reviews'),
(127, 'Sarawak', 'Beach', 'Damai Beach Resort Strip', 'Scenic sandy stretch sitting below the shadow of Mount Santubong\'s rainforest edges.', '3-5 Hours', 'https://maps.google.com', 'damai_beach.jpeg', 4.4, '1,120 reviews'),
(128, 'Sarawak', 'Beach', 'Tusan Cliff Beach', 'Dramatic limestone cliffs famously known for bright blue-tears ocean bioluminescence events.', '2 Hours', 'https://maps.google.com', 'tusan.jpg', 4.5, '940 reviews'),
(129, 'Sarawak', 'Beach', 'Talang-Talang Islands', 'Protected marine boundaries serving as sanctuaries for endangered green sea turtles.', '4-6 Hours', 'https://maps.google.com', 'talang.jpeg', 4.6, '310 reviews'),
(130, 'Sarawak', 'Beach', 'Pantai Pasir Panjang', 'Quiet, uncrowded beachfront ideal for observing serene evening tide patterns.', '2 Hours', 'https://maps.google.com', 'pasirpanjang.jpg', 4.1, '430 reviews'),
(131, 'Sarawak', 'Nature', 'Mulu Caves National Park', 'UNESCO treasure hosting the world\'s largest cave chambers and massive bat migrations.', '1-2 Days', 'https://maps.google.com', 'guaniah.png', 4.9, '1,980 reviews'),
(132, 'Sarawak', 'Nature', 'Bako National Park Trails', 'Sarawak\'s oldest park, famous for strange stone sea-stacks and wild proboscis monkeys.', '4-6 Hours', 'https://maps.google.com', 'bako.jpg', 4.8, '2,100 reviews'),
(133, 'Sarawak', 'Nature', 'Semenggoh Wildlife Centre', 'Established sanctuary to witness majestic semi-wild orangutans coming down for fruit feeds.', '2 Hours', 'https://maps.google.com', 'semenggoh.jpeg', 4.7, '1,450 reviews'),
(134, 'Sarawak', 'Nature', 'Niah National Park Caves', 'Massive limestone structures tracking prehistoric human settlement remains dating back 40,000 years.', '4-5 Hours', 'https://maps.google.com', 'niah.jpeg', 4.6, '890 reviews'),
(135, 'Sarawak', 'Adventure', 'Mulu Pinnacles Climb', 'Demanding multi-day vertical trek exploring razor-sharp limestone rock spires rising over tree canopies.', '2 Days', 'https://maps.google.com', 'mulu_pinnacles.jpeg', 4.8, '340 reviews'),
(136, 'Sarawak', 'Adventure', 'Santubong River Kayaking', 'Paddle through winding mangrove streams searching for rare Irrawaddy river dolphins.', '3-4 Hours', 'https://maps.google.com', 'santubong_kayak.jpeg', 4.5, '540 reviews'),
(137, 'Sarawak', 'Adventure', 'Bengkoh Lake Kayak Eco Tour', 'Glide across clear, calm mountain reservoir waters past flooded ghost forest trees.', '4 Hours', 'https://maps.google.com', 'bengkoh.jpeg', 4.7, '220 reviews'),
(138, 'Sarawak', 'Adventure', 'Hiking Mount Santubong Peak', 'Strenuous vertical climb utilizing rope ladders and muddy root holds to reach high panoramic viewpoints.', '5-7 Hours', 'https://maps.google.com', 'santubong_peak.jpeg', 4.6, '610 reviews'),
(139, 'Sarawak', 'Culture', 'Kuching Waterfront & Old Court', 'Stroll down historic river paths adjacent to traditional colonial administrative hubs.', '2-3 Hours', 'https://maps.google.com', 'kuching_waterfront.jpg', 4.6, '2,800 reviews'),
(140, 'Sarawak', 'Culture', 'Sarawak Cultural Village', 'Living museum showing true lifestyle setups of 7 distinct local tribal communities.', '3-4 Hours', 'https://maps.google.com', 'sarawak_cultural.jpg', 4.8, '2,100 reviews'),
(141, 'Sarawak', 'Culture', 'Sibu Night Market Hub', 'Energetic market row serving authentic Foochow street food, snacks, and steamed buns.', '1-2 Hours', 'https://maps.google.com', 'sibu_market.jpeg', 4.5, '1,120 reviews'),
(142, 'Sarawak', 'Culture', 'Borneo Cultures Museum', 'State-of-the-art exhibition center displaying precious historic artifacts and native textiles.', '2-3 Hours', 'https://maps.google.com', 'borneo_museum.jpeg', 4.8, '1,980 reviews'),
(143, 'Sarawak', 'Food', 'Sarawak Laksa & Kolo Mee', 'Taste Kuching\'s legendary herbal spicy laksa noodle broth and dry tossed minced meat noodles.', '1-2 Hours', 'https://maps.google.com', 'sarawak_laksa.jpeg', 4.9, '3,900 reviews'),
(144, 'Sarawak', 'Food', 'Ayam Pansuh & Midin Ferns', 'Authentic tribal bamboo-cooked chicken and crispy jungle ferns stir-fried with shrimp paste.', '1-2 Hours', 'https://maps.google.com', 'pansuh.jpeg', 4.8, '920 reviews'),
(145, 'Kedah', 'Beach', 'Langkawi Island (Pantai Cenang)', 'Kedah\'s premier lively coastline offering soft sand beds and exciting water sport options.', '3-5 Hours', 'https://maps.google.com', 'langkawi.png', 4.6, '1,740 reviews'),
(146, 'Kedah', 'Beach', 'Tanjung Rhu Beach Hideout', 'Serene, premium beach strip flanked by unique limestone islands and calm waters.', '2-3 Hours', 'https://maps.google.com', 'tanjung_rhu.jpeg', 4.7, '1,200 reviews'),
(147, 'Kedah', 'Beach', 'Pantai Tengah Beach', 'Peaceful alternative to Cenang, ideal for catching quiet, unobstructed sunset views.', '2 Hours', 'https://maps.google.com', 'pantai_tengah.jpg', 4.4, '830 reviews'),
(148, 'Kedah', 'Beach', 'Pulau Payar Marine Park', 'Exceptional island sanctuary hosting shallow coral reefs and encounters with baby blacktip sharks.', '5-8 Hours', 'https://maps.google.com', 'pulau_payar.jpeg', 4.5, '940 reviews'),
(149, 'Kedah', 'Nature', 'Kilim Geoforest Mangrove Tour', 'Boat safari through ancient limestone mangrove gorges to observe wild eagles feeding.', '2-3 Hours', 'https://maps.google.com', 'kilim.jpeg', 4.7, '1,890 reviews'),
(150, 'Kedah', 'Nature', 'Mount Jerai Resort Peak (Gunung Jerai)', 'Drive up a cool mountain peak overlooking endless emerald paddy field layouts.', '3-4 Hours', 'https://maps.google.com', 'gunung_jerai.jpg', 4.5, '1,100 reviews'),
(151, 'Kedah', 'Nature', 'Telaga Tujuh Seven Wells Waterfall', 'Natural freshwater pools fed by seven connected mountain streams deep in the jungle.', '2-3 Hours', 'https://maps.google.com', 'seven_wells.jpeg', 4.6, '1,320 reviews'),
(152, 'Kedah', 'Nature', 'Ulu Muda Forest Reserve', 'Deep, pristine wilderness shelter tracking wild elephants, salt licks, and rare birds.', '1-2 Days', 'https://maps.google.com', 'ulu_muda.jpeg', 4.5, '280 reviews'),
(153, 'Kedah', 'Adventure', 'Langkawi SkyCab & SkyBridge Walkway', 'Ride a high cable car to step on a dramatic curved suspension bridge hanging over canyons.', '3-4 Hours', 'https://maps.google.com', 'skybridge.jpeg', 4.8, '4,100 reviews'),
(154, 'Kedah', 'Adventure', 'Jet Ski Island Hopping Safari', 'High-speed personal watercraft tour navigating around hidden rock formations and lake islands.', '3-4 Hours', 'https://maps.google.com', 'jetski_safari.jpeg', 4.7, '780 reviews'),
(155, 'Kedah', 'Adventure', 'Skytrex Adventure Langkawi', 'Challenging tree-to-tree obstacle rope courses suspended inside a dense rainforest canopy.', '3 Hours', 'https://maps.google.com', 'skytrex_langkawi.jpeg', 4.6, '430 reviews'),
(156, 'Kedah', 'Adventure', 'Ziplining Umgawa Eco Adventures', 'Fly along high-speed steel cable courses tracking pristine waterfall gorge viewpoints.', '2-3 Hours', 'https://maps.google.com', 'umgawa.jpeg', 4.7, '310 reviews'),
(157, 'Kedah', 'Culture', 'Alor Setar Paddy Museum & Tower', 'Unique cultural venue detailing historical rice cultivation alongside a panoramic tower view.', '2-3 Hours', 'https://maps.google.com', 'paddy_museum.jpg', 4.4, '650 reviews'),
(158, 'Kedah', 'Culture', 'Lembah Bujang Archaeological Site', 'Explore the ruins of a Hindu-Buddhist kingdom dating back over 2,000 years.', '2 Hours', 'https://maps.google.com', 'lembah_bujang.jpeg', 4.5, '450 reviews'),
(159, 'Kedah', 'Culture', 'Mahsuri Cultural Tomb Centre', 'Historical courtyard complex documenting the famous tragic legend of Langkawi\'s folklore.', '1-2 Hours', 'https://maps.google.com', 'mahsuri.jpeg', 4.4, '1,100 reviews'),
(160, 'Kedah', 'Culture', 'Zahir Mosque Historic Architecture', 'One of Malaysia\'s oldest and finest Moorish-style religious structures with black domes.', '1 Hour', 'https://maps.google.com', 'zahir_mosque.jpeg', 4.8, '1,950 reviews'),
(161, 'Kedah', 'Food', 'Alor Setar Nasi Lemak Royale', 'Classic yellow rice paired with rich, sweet-spicy thick gravies and assorted side dishes.', '1 Hour', 'https://maps.google.com', 'lemak_royale.jpeg', 4.8, '2,300 reviews'),
(162, 'Kedah', 'Food', 'Laksa Kedah Teluk Kechai', 'Fragrant rice noodles served in thick spicy fish broth topped with local coconut sambal.', '1 Hour', 'https://maps.google.com', 'laksa_kedah.jpeg', 4.7, '1,900 reviews'),
(163, 'Kelantan', 'Beach', 'Pantai Cahaya Bulan (PCB Beach)', 'Famous local beach lined with traditional snack stalls serving fresh seafood.', '1-2 Hours', 'https://maps.google.com', 'gunungkelantan.png', 4.7, '1,310 reviews'),
(164, 'Kelantan', 'Beach', 'Pantai Senok (Pine Tree Forest Beach)', 'Beautiful coastal stretch closely packed with tall casuarina trees resembling a Nami Island look.', '1-2 Hours', 'https://maps.google.com', 'pantaisenok.jpeg', 4.4, '1,500 reviews'),
(165, 'Kelantan', 'Beach', 'Pantai Melawi Shoreline', 'Tranquil sandy beach ideal for relaxing evening walks away from crowds.', '2 Hours', 'https://maps.google.com', 'default_place.jpg', 4.1, '430 reviews'),
(166, 'Kelantan', 'Beach', 'Pantai Sri Tujuh Lagoon', 'Unique coastal border zone hosting large artificial lagoons and regular boat festivals.', '2 Hours', 'https://maps.google.com', 'sritujuh.jpeg', 4.3, '890 reviews'),
(167, 'Kelantan', 'Nature', 'Gunung Stong State Park Wilds', 'Home to the massive seven-tiered Jelawang Waterfall, one of the highest in Southeast Asia.', '1-2 Days', 'https://maps.google.com', 'stong.jpeg', 4.8, '540 reviews'),
(168, 'Kelantan', 'Nature', 'Lata Rek Eco Forest Waterfall', 'Popular tiered rocky cascade forming refreshing natural mountain river pools.', '2-3 Hours', 'https://maps.google.com', 'latarek.jpeg', 4.4, '650 reviews'),
(169, 'Kelantan', 'Nature', 'Gua Ikan Limestone Caves', 'Fascinating natural cavern system named after unique fish-shaped entrance rock alignments.', '2 Hours', 'https://maps.google.com', 'guaikan.jpeg', 4.3, '310 reviews'),
(170, 'Kelantan', 'Nature', 'Bukit Marak Mountain Trails', 'Historic hill climb tracking local princess legends and panoramic countryside views.', '2 Hours', 'https://maps.google.com', 'bukit_marak.jpeg', 4.2, '450 reviews'),
(171, 'Kelantan', 'Adventure', 'Nenggiri River Adventure Trail', 'Exciting bamboo rafting or kayaking trip through historic limestone caves and rainforests.', '4-6 Hours', 'https://maps.google.com', 'nenggiri.jpg', 4.7, '340 reviews'),
(172, 'Kelantan', 'Adventure', 'Hiking Gunung Stong Peak', 'Demanding mountain trek scaling slippery rock steep zones to camp above clouds.', '5-8 Hours', 'https://maps.google.com', 'stong_hiking.jpeg', 4.6, '220 reviews'),
(173, 'Kelantan', 'Adventure', 'Jungle Railway Train Experience', 'Scenic rural diesel train trip navigating through small inland settlements and forest bridges.', '3-5 Hours', 'https://maps.google.com', 'jungle_railway.jpeg', 4.5, '540 reviews'),
(174, 'Kelantan', 'Adventure', 'River Tubing Dabong', 'Fun, splashing ride floating down clear mountain streams wrapped in forest greenery.', '2-3 Hours', 'https://maps.google.com', 'dabong_tubing.jpeg', 4.6, '310 reviews'),
(175, 'Kelantan', 'Culture', 'Siti Khadijah Market', 'Vibrant octagonal market hub famously run mostly by enterprising local women traders.', '2-3 Hours', 'https://maps.google.com', 'sitikhadijah.jpg', 4.7, '3,200 reviews'),
(176, 'Kelantan', 'Culture', 'Wat Photivihan (Sleeping Buddha)', 'Impressive temple complex hosting a massive 40-meter reclining Buddha statue structure.', '1 Hour', 'https://maps.google.com', 'photivihan.jpeg', 4.6, '1,450 reviews'),
(177, 'Kelantan', 'Culture', 'Kampung Wau Craft Artisan Workshop', 'Watch master craftsmen assemble large traditional Malaysian kites (Wau Bulan) using paper filigree.', '1-2 Hours', 'https://maps.google.com', 'wau_craft.jpeg', 4.8, '540 reviews'),
(178, 'Kelantan', 'Culture', 'Istana Jahar (Customs Museum)', 'Beautiful wooden royal palace detailing detailed classic carvings and traditional ceremonies.', '1-2 Hours', 'https://maps.google.com', 'istana_jahar.jpeg', 4.5, '720 reviews'),
(179, 'Kelantan', 'Food', 'Nasi Kerabu Siti Khadijah', 'Traditional blue-colored rice infused with wild herbs, served with salted fish, coconut shreds, and solok lada.', '1 Hour', 'https://maps.google.com', 'nasikerabu.jpeg', 4.9, '3,600 reviews'),
(180, 'Kelantan', 'Food', 'Laksam & Akok Kelantan', 'Steamed rice rolls in creamy white fish gravy paired with sweet, rich baked duck-egg akok custards.', '1 Hour', 'https://maps.google.com', 'laksam_akok.jpeg', 4.8, '2,200 reviews');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `billcode` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `booking_no` varchar(50) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `booking_status` enum('Confirmed','Cancelled','Completed') DEFAULT 'Confirmed',
  `total_budget` decimal(10,2) DEFAULT 2000.00,
  `finished_at` datetime DEFAULT NULL,
  `payment_plan` enum('full','installment') DEFAULT 'full',
  `settlement_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `homestay_id`, `check_in`, `check_out`, `guests`, `total_price`, `status`, `payment_status`, `billcode`, `created_at`, `booking_no`, `room_id`, `booking_status`, `total_budget`, `finished_at`, `payment_plan`, `settlement_id`) VALUES
(1, 2, 11, '2026-07-01', '2026-07-02', 1, 217.80, 'Confirmed', 'Paid', '1lvdfs2m', '2026-07-01 07:06:31', NULL, NULL, 'Confirmed', 2000.00, NULL, 'full', 3),
(2, 2, 11, '2026-07-01', '2026-07-04', 1, 653.40, 'Pending', 'Unpaid', NULL, '2026-07-02 01:25:22', NULL, NULL, 'Confirmed', 2000.00, NULL, 'full', 1),
(3, 2, 9, '2026-07-04', '2026-07-06', 2, 352.00, 'Confirmed', 'Paid', '5ulfvdhd', '2026-07-04 10:36:11', 'VYG-BKG-20260704-4108', NULL, 'Confirmed', 200.00, NULL, 'full', NULL),
(4, 2, 9, '2026-07-08', '2026-07-10', 1, 352.00, 'Confirmed', 'Paid', '9kh1nms6', '2026-07-07 11:09:12', 'VYG-BKG-20260707-1033', NULL, 'Confirmed', 2000.00, NULL, 'installment', 2),
(5, 2, 9, '2026-12-03', '2026-12-10', 4, 1232.00, 'Pending', '', NULL, '2026-07-08 01:12:48', 'VYG-BKG-20260708-2784', NULL, 'Confirmed', 2000.00, NULL, 'installment', NULL),
(6, 2, 11, '2026-07-16', '2026-07-18', 4, 435.60, 'Confirmed', 'Paid', 'iox8o7xf', '2026-07-08 01:13:09', 'VYG-BKG-20260708-4266', NULL, 'Confirmed', 2000.00, NULL, 'full', NULL),
(7, 2, 11, '2026-11-09', '2026-11-12', 3, 653.40, 'Pending', '', NULL, '2026-07-08 01:14:58', 'VYG-BKG-20260708-8373', NULL, 'Confirmed', 2000.00, NULL, 'installment', NULL),
(8, 2, 9, '2026-08-27', '2026-08-29', 3, 352.00, 'Confirmed', 'Paid', 'ao1uj1kq', '2026-07-08 01:15:26', 'VYG-BKG-20260708-2140', NULL, 'Confirmed', 2000.00, NULL, 'full', NULL),
(9, 2, 12, '2026-07-18', '2026-07-20', 6, 484.00, 'Pending', '', NULL, '2026-07-11 06:03:30', 'VYG-BKG-20260711-7737', 1, 'Confirmed', 2000.00, NULL, 'full', NULL),
(10, 2, 12, '2026-07-12', '2026-07-13', 6, 242.00, 'Confirmed', 'Paid', 'he6mjurv', '2026-07-11 06:03:56', 'VYG-BKG-20260711-5553', 1, 'Confirmed', 2000.00, NULL, 'full', NULL),
(11, 2, 12, '2026-07-18', '2026-07-19', 5, 440.00, 'Confirmed', 'Paid', 'hbbbhco4', '2026-07-11 06:32:39', 'VYG-BKG-20260711-2654', 3, 'Confirmed', 2000.00, NULL, 'full', NULL),
(12, 2, 12, '2026-07-24', '2026-07-25', 1, 242.00, 'Confirmed', 'Paid', 'm8frvak6', '2026-07-11 06:37:32', 'VYG-BKG-20260711-9340', 1, 'Confirmed', 850.00, NULL, 'full', NULL),
(13, 2, 12, '2026-07-16', '2026-07-17', 1, 242.00, 'Pending', '', NULL, '2026-07-11 06:39:06', 'VYG-BKG-20260711-3227', 1, 'Confirmed', 2000.00, NULL, 'full', NULL),
(14, 11, 12, '2026-07-12', '2026-07-13', 8, 385.00, 'Pending', '', NULL, '2026-07-11 10:58:42', 'VYG-BKG-20260711-5154', 2, 'Confirmed', 2000.00, NULL, 'full', NULL),
(15, 2, 12, '2026-07-18', '2026-07-19', 1, 385.00, 'Confirmed', 'Paid', 'vnbwfdyx', '2026-07-11 11:06:20', 'VYG-BKG-20260711-1039', 2, 'Confirmed', 2000.00, NULL, 'full', NULL),
(16, 11, 23, '2026-10-11', '2026-10-13', 5, 396.00, 'Confirmed', 'Paid', 'c6htvym4', '2026-07-11 12:45:18', 'VYG-BKG-20260711-4868', 15, 'Confirmed', 980.00, NULL, 'full', NULL),
(17, 11, 22, '2026-07-25', '2026-07-26', 5, 308.00, 'Confirmed', 'Paid', 'v4ys90rl', '2026-07-11 12:53:57', 'VYG-BKG-20260711-7453', NULL, 'Confirmed', 2000.00, NULL, 'full', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_installments`
--

CREATE TABLE `booking_installments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `installment_no` int(11) NOT NULL COMMENT '1=first, 2=second, 3=third',
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` datetime DEFAULT NULL,
  `status` enum('Pending','Paid','Overdue') DEFAULT 'Pending',
  `billcode` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_installments`
--

INSERT INTO `booking_installments` (`id`, `booking_id`, `user_id`, `installment_no`, `amount`, `due_date`, `paid_date`, `status`, `billcode`, `created_at`) VALUES
(1, 4, 1, 1, 176.00, '2026-07-07', '2026-07-08 01:14:55', 'Paid', NULL, '2026-07-07 17:14:55'),
(2, 4, 1, 2, 88.00, '2026-08-07', NULL, 'Pending', NULL, '2026-07-07 17:14:55'),
(3, 4, 1, 3, 88.00, '2026-09-07', NULL, 'Pending', NULL, '2026-07-07 17:14:55'),
(4, 5, 2, 1, 616.00, '2026-07-08', NULL, 'Pending', NULL, '2026-07-08 01:12:48'),
(5, 5, 2, 2, 308.00, '2026-08-08', NULL, 'Pending', NULL, '2026-07-08 01:12:48'),
(6, 5, 2, 3, 308.00, '2026-09-08', NULL, 'Pending', NULL, '2026-07-08 01:12:48'),
(7, 7, 2, 1, 326.70, '2026-07-08', NULL, 'Pending', NULL, '2026-07-08 01:14:58'),
(8, 7, 2, 2, 163.35, '2026-08-08', NULL, 'Pending', NULL, '2026-07-08 01:14:58'),
(9, 7, 2, 3, 163.35, '2026-09-08', NULL, 'Pending', NULL, '2026-07-08 01:14:58');

-- --------------------------------------------------------

--
-- Table structure for table `homestays`
--

CREATE TABLE `homestays` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` enum('Apartment','Villa','Resort','Cabin','Chalet','Traditional House') NOT NULL,
  `description` text NOT NULL,
  `max_guests` int(11) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `state` varchar(50) NOT NULL,
  `district` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `maps_link` text DEFAULT NULL,
  `facilities` text NOT NULL,
  `cover_image` varchar(255) NOT NULL,
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `approval_status` enum('Registered','Pending Approval','Published','Rejected','Draft','Live','Approved') DEFAULT 'Registered',
  `completion_score` int(11) DEFAULT 40,
  `reject_reason` text DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `revenue` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL,
  `map_link` text DEFAULT NULL,
  `pricing_type` enum('Whole House','Per Room','Both') DEFAULT 'Whole House',
  `total_rooms` int(11) DEFAULT 1,
  `main_image` varchar(255) DEFAULT NULL,
  `facility_images` text DEFAULT NULL,
  `listing_fee_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `total_budget` decimal(10,2) DEFAULT 2000.00,
  `finished_at` datetime DEFAULT NULL,
  `ic_copy` varchar(500) DEFAULT '',
  `utility_bill` varchar(500) DEFAULT '',
  `ssm_doc` varchar(500) DEFAULT '',
  `business_license` varchar(500) DEFAULT '',
  `ownership_proof` varchar(500) DEFAULT '',
  `availability_rule` enum('exclusive','separate') DEFAULT 'exclusive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homestays`
--

INSERT INTO `homestays` (`id`, `user_id`, `name`, `category`, `description`, `max_guests`, `price_per_night`, `state`, `district`, `address`, `maps_link`, `facilities`, `cover_image`, `payment_status`, `approval_status`, `completion_score`, `reject_reason`, `views`, `revenue`, `created_at`, `image_url`, `map_link`, `pricing_type`, `total_rooms`, `main_image`, `facility_images`, `listing_fee_status`, `total_budget`, `finished_at`, `ic_copy`, `utility_bill`, `ssm_doc`, `business_license`, `ownership_proof`, `availability_rule`) VALUES
(12, 7, 'Homestay Pekan Pahang Semi‑D', 'Chalet', 'Facilities: BBQ pit, playground, rabbit pen, fishing pond, riverside access, fruit orchard, large parking area.', 25, 1600.00, 'Pahang', 'Bentong', 'Lot 123, Jalan Cemperoh 1, Kampung Janda Baik, 28750 Bentong, Pahang, Malaysia', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.728!2d101.827!3d3.370!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc3f0f0f0f0f%3A0xabcdef123456789!2sSri%20Chempaka%20Homestay%20Janda%20Baik!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Swimming Pool\",\"Kitchen\",\"Air Conditioner\",\"TV\",\"BBQ\",\"Parking\"]', 'uploads/1783749474_3645_pahang 3.webp', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 05:57:54', NULL, NULL, 'Per Room', 3, 'uploads/1783749474_3645_pahang 3.webp', '[\"uploads\\/1783749474_9561_pahang 3.webp\",\"uploads\\/1783749474_3399_pahang2.jpg\",\"uploads\\/1783749474_9011_pahang1.jpeg\"]', 'Paid', 2000.00, NULL, 'uploads/1783749474_9108_ic.png', 'uploads/1783749474_1957_bil.png', 'uploads/1783749474_5410_sijilssm.webp', 'uploads/1783749474_3543_local.png', 'uploads/1783749474_6598_local.png', 'exclusive'),
(13, 10, 'Sri Chempaka Homestay', 'Chalet', 'Located in a quiet village by the river, surrounded by lush greenery and cool mountain air. Perfect for family day events.', 4, 150.00, 'Pahang', 'Janda Baik', 'Lot 123, Jalan Cemperoh 1, Kampung Janda Baik, 28750 Bentong, Pahang, Mala', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.728!2d101.827!3d3.370!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sSri%20Chempaka%20Homestay%20Janda%20Baik!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Swimming Pool\",\"Kitchen\"]', 'uploads/1783768870_5969_hstay2.jpg', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 11:21:10', NULL, NULL, 'Per Room', 3, 'uploads/1783768870_5969_hstay2.jpg', '[\"uploads\\/1783768870_8563_hstay1.jpeg\",\"uploads\\/1783768870_8443_hstay2.jpg\",\"uploads\\/1783768870_5529_hstay3.jpeg\"]', 'Paid', 2000.00, NULL, 'uploads/1783768870_4262_1783749474_9108_ic.png', 'uploads/1783768870_8013_1783749474_1957_bil.png', 'uploads/1783768870_1344_1783749474_5410_sijilssm.webp', 'uploads/1783768870_1525_1783749474_6598_local.png', 'uploads/1783768870_5569_1783749474_3543_local.png', 'exclusive'),
(14, 10, 'Bayt Rizq Cameron Highlands', 'Villa', 'Located in Brichang town , walking distance to Kea Farm Market and close to Boh Tea plantation , Mossy forest and vegetables farm . Cool highland climate and perfect for family retreats or group stays', 8, 180.00, 'Pahang', 'Brinchang', 'No. 45, Jalan Kea Farm, Brinchang, 39100 Cameron Highlands, Pahang, Malaysia', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d101.389!3d4.490!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sBayt%20Rizq%20Homestay%20Cameron%20Highlands!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Kitchen\",\"Parking\"]', 'uploads/1783769310_4308_hstay4.webp', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 11:28:30', NULL, NULL, 'Whole House', 1, 'uploads/1783769310_4308_hstay4.webp', '[\"uploads\\/1783769310_6448_hstay6.jpg\",\"uploads\\/1783769310_6379_hstay5.jpg\",\"uploads\\/1783769310_2569_hstay4.webp\"]', 'Paid', 2000.00, NULL, 'uploads/1783769310_6806_1783749474_9108_ic.png', 'uploads/1783769310_8902_1783749474_1957_bil.png', 'uploads/1783769310_6268_1783749474_5410_sijilssm.webp', 'uploads/1783769310_9076_1783749474_6598_local.png', 'uploads/1783769310_3584_1783749474_3543_local.png', 'exclusive'),
(15, 7, 'G Residence, Menara B, Jalan Mutiara 7, Johor Bahru, Johor, Malaysia', 'Chalet', 'Located ~6 km from Austin Heights Water Park.\r\n\r\nShort drive to Johor Bahru city centre and CIQ Causeway (Singapore access).\r\n\r\nNearby shopping malls: AEON Tebrau, IKEA Tebrau, Toppen Shopping Centre.\r\n\r\nQuiet residential environment with modern amenities.', 4, 200.00, 'Johor', 'Johor Bahru', 'G Residence, Menara B, Jalan Mutiara 7, Johor Bahru, Johor, Malaysia', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"BBQ\",\"Parking\"]', 'uploads/1783769970_6773_hstay7.avif', 'Unpaid', 'Registered', 85, NULL, 0, 0.00, '2026-07-11 11:39:30', NULL, NULL, 'Whole House', 1, 'uploads/1783769970_6773_hstay7.avif', '[\"uploads\\/1783769970_4232_hstay9.jpg\",\"uploads\\/1783769970_1888_hstay8.jpg\",\"uploads\\/1783769970_1601_hstay7.avif\"]', 'Unpaid', 2000.00, NULL, 'uploads/1783769970_9064_1783749474_9108_ic.png', 'uploads/1783769970_9857_1783749474_1957_bil.png', 'uploads/1783769970_3420_1783749474_5410_sijilssm.webp', 'uploads/1783769970_3308_1783749474_3543_local.png', 'uploads/1783769970_8172_1783749474_6598_local.png', 'exclusive'),
(16, 7, 'G Residence, Menara B, Jalan Mutiara 7, Johor Bahru, Johor, Malaysia', 'Chalet', 'Located ~6 km from Austin Heights Water Park.\r\n\r\nShort drive to Johor Bahru city centre and CIQ Causeway (Singapore access).\r\n\r\nNearby shopping malls: AEON Tebrau, IKEA Tebrau, Toppen Shopping Centre.\r\n\r\nQuiet residential environment with modern amenities.', 4, 200.00, 'Johor', 'Johor Bahru', 'G Residence, Menara B, Jalan Mutiara 7, Johor Bahru, Johor, Malaysia', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"BBQ\",\"Parking\"]', 'uploads/1783769971_5943_hstay7.avif', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 11:39:31', NULL, NULL, 'Whole House', 1, 'uploads/1783769971_5943_hstay7.avif', '[\"uploads\\/1783769971_4993_hstay9.jpg\",\"uploads\\/1783769971_5977_hstay8.jpg\",\"uploads\\/1783769971_7838_hstay7.avif\"]', 'Paid', 2000.00, NULL, 'uploads/1783769971_4429_1783749474_9108_ic.png', 'uploads/1783769971_2456_1783749474_1957_bil.png', 'uploads/1783769971_8194_1783749474_5410_sijilssm.webp', 'uploads/1783769971_1699_1783749474_3543_local.png', 'uploads/1783769971_4389_1783749474_6598_local.png', 'exclusive'),
(17, 5, 'Home2828Stay', '', '22 km from Sibu Airport, easy access to local attractions, quiet residential area', 10, 500.00, 'Sarawak', 'Sibu', '22 km from Sibu Airport, easy access to local attractions, quiet residential area', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy\"', '[\"WiFi\",\"Kitchen\",\"Air Conditioner\"]', 'uploads/1783770587_5990_hstay10.jpg', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 11:49:47', NULL, NULL, 'Per Room', 3, 'uploads/1783770587_5990_hstay10.jpg', '[\"uploads\\/1783770587_2825_hstay13.jpeg\",\"uploads\\/1783770587_2819_hstay11.jpg\",\"uploads\\/1783770587_1275_hstay10.jpg\"]', 'Paid', 2000.00, NULL, 'uploads/1783770587_4217_1783749474_9108_ic.png', 'uploads/1783770587_2065_1783749474_1957_bil.png', 'uploads/1783770587_8903_1783749474_3543_local.png', 'uploads/1783770587_7131_1783749474_5410_sijilssm.webp', 'uploads/1783770587_6976_1783749474_3543_local.png', 'exclusive'),
(18, 5, 'D’Warisan Homestay Kuala Terengganu', 'Chalet', 'Near Tok Jembal Beach, full kitchen, WiFi, parking, quiet village area.', 8, 300.00, 'Terengganu', 'Kuala Terengganu', 'Lot 45, Kampung Tok Jembal, 21300 Kuala Terengganu, Terengganu', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"BBQ\",\"Parking\"]', 'uploads/1783771660_5720_hstay1.jpg', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 12:07:40', NULL, NULL, 'Whole House', 1, 'uploads/1783771660_5720_hstay1.jpg', '[\"uploads\\/1783771660_7469_1783770587_1275_hstay10.jpg\",\"uploads\\/1783771660_8825_1783770587_2819_hstay11.jpg\",\"uploads\\/1783771660_1835_1783770587_2825_hstay13.jpeg\"]', 'Paid', 2000.00, NULL, 'uploads/1783771660_9594_1783749474_9108_ic.png', 'uploads/1783771660_7838_1783749474_1957_bil.png', 'uploads/1783771660_8090_1783749474_3543_local.png', 'uploads/1783771660_3133_1783749474_5410_sijilssm.webp', 'uploads/1783771660_1443_1783749474_6598_local.png', 'exclusive'),
(19, 7, 'Pantai Batu Buruk Inn (Per Room', 'Villa', 'Sea view, walking distance to beach, attached bathroom.', 4, 150.00, 'Terengganu', 'Kuala Terengganu', 'Jalan Pantai Batu Buruk, 20400 Kuala Terengganu, Terengganu', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Air Conditioner\",\"BBQ\"]', 'uploads/1783771903_3327_hstay 3.jpg', 'Paid', 'Pending Approval', 85, NULL, 0, 0.00, '2026-07-11 12:11:43', NULL, NULL, 'Per Room', 2, 'uploads/1783771903_3327_hstay 3.jpg', '[\"uploads\\/1783771903_7985_hstay 3.jpg\",\"uploads\\/1783771903_3968_hstay 2.jpg\",\"uploads\\/1783771903_5604_hstay1.jpg\"]', 'Paid', 2000.00, NULL, 'uploads/1783771903_6213_1783749474_9108_ic.png', 'uploads/1783771903_2659_1783749474_1957_bil.png', 'uploads/1783771903_1993_1783749474_3543_local.png', 'uploads/1783771903_3273_1783749474_5410_sijilssm.webp', 'uploads/1783771903_2368_1783749474_6598_local.png', 'exclusive'),
(20, 5, 'Taiping Lake View Homestay (Whole House)', 'Chalet', '⭐ Overlooks Taiping Lake Gardens, BBQ area, garden, near Zoo Taiping.', 8, 350.00, 'Perak', 'Taiping', 'No. 12, Lorong Taman Tasik, 34000 Taiping, Perak', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Kitchen\",\"BBQ\"]', 'uploads/1783772399_9644_hstay 4.jpeg', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 12:19:59', NULL, NULL, 'Whole House', 1, 'uploads/1783772399_9644_hstay 4.jpeg', '[\"uploads\\/1783772399_2958_hstay 3.jpg\",\"uploads\\/1783772399_8201_hstay 2.jpg\",\"uploads\\/1783772399_3550_hstay1.jpg\"]', 'Paid', 2000.00, NULL, 'uploads/1783772399_4204_1783749474_9108_ic.png', 'uploads/1783772399_8303_1783749474_1957_bil.png', 'uploads/1783772399_2884_1783749474_5410_sijilssm.webp', 'uploads/1783772399_4654_1783749474_3543_local.png', 'uploads/1783772399_6253_1783749474_6598_local.png', 'exclusive'),
(21, 10, 'Azlam Valley', 'Chalet', '⭐ Central Ipoh, WiFi, shared kitchen, walking distance to cafes.', 4, 150.00, 'Perak', 'Ipoh', 'Jalan Sultan Azlan Shah, 31400 Ipoh, Perak', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2sm', '[\"WiFi\",\"TV\",\"Parking\"]', 'uploads/1783772950_3136_hstay 5.webp', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 12:29:10', NULL, NULL, 'Per Room', 2, 'uploads/1783772950_3136_hstay 5.webp', '[\"uploads\\/1783772950_5116_hstay 3.jpg\",\"uploads\\/1783772950_4331_hstay 2.jpg\",\"uploads\\/1783772950_1839_hstay1.jpg\"]', 'Paid', 2000.00, NULL, 'uploads/1783772950_2269_1783749474_9108_ic.png', 'uploads/1783772950_1791_1783749474_1957_bil.png', 'uploads/1783772950_4328_1783749474_3543_local.png', 'uploads/1783772950_9908_1783749474_5410_sijilssm.webp', 'uploads/1783772950_5334_1783749474_6598_local.png', 'exclusive'),
(22, 10, 'Sekeping Serendah', 'Chalet', 'Glass‑and‑steel jungle retreat, open‑concept rooms, plunge pool, rainforest trekking.', 6, 280.00, 'Selangor', 'Sekinchan', 'No. 8, Jalan Sekeping serendah 2, 47810  Selangor', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Kitchen\",\"Parking\"]', 'uploads/1783773194_4486_hstay 6.avif', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 12:33:14', NULL, NULL, 'Whole House', 1, 'uploads/1783773194_4486_hstay 6.avif', '[\"uploads\\/1783773194_3286_hstayy.avif\",\"uploads\\/1783773194_8410_hstay 2.jpg\",\"uploads\\/1783773194_9620_hstay7.jpg\"]', 'Paid', 2000.00, NULL, 'uploads/1783773194_4466_1783749474_9108_ic.png', 'uploads/1783773194_2807_1783749474_1957_bil.png', 'uploads/1783773194_4290_1783749474_5410_sijilssm.webp', 'uploads/1783773194_7561_1783749474_3543_local.png', 'uploads/1783773194_8989_1783749474_6598_local.png', 'exclusive'),
(23, 5, 'Kinabalu Mountain View Homestay Kundasang (Whole House)', 'Chalet', '⭐ Scenic mountain view, BBQ area, garden, cool weather.', 4, 150.00, 'Sabah', 'Ranau', 'Kundasang, 89308 Ranau, Sabah', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Air Conditioner\",\"BBQ\"]', 'uploads/1783773815_3650_hstay sabah.webp', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 12:43:35', NULL, NULL, 'Per Room', 2, 'uploads/1783773815_3650_hstay sabah.webp', '[\"uploads\\/1783773815_3058_hstay10.jpg\",\"uploads\\/1783773815_9929_hstay11.jpg\",\"uploads\\/1783773815_6961_hstay13.jpeg\"]', 'Paid', 2000.00, NULL, 'uploads/1783773815_7148_1783749474_9108_ic.png', 'uploads/1783773815_9966_1783749474_1957_bil.png', 'uploads/1783773815_2063_1783749474_5410_sijilssm.webp', 'uploads/1783773815_6730_1783749474_6598_local.png', 'uploads/1783773815_2303_1783749474_3543_local.png', 'exclusive'),
(24, 10, 'Seaview Homestay Batu Ferringhi (Whole House)', 'Chalet', '⭐ Balcony sea view, BBQ area, WiFi, near beach & night market.', 4, 150.00, 'Pulau Pinang', 'Batu Feringgi', 'Jalan Sungai Emas, 11100 Batu Ferringhi, Pulau Pinang', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7962.123456789!2d103.789!3d1.492!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sYangtze%20Home%20Stay%20Johor%20Bahru!5e0!3m2!1sen!2smy!4v1719570000000!5m2!1sen!2smy', '[\"WiFi\",\"Swimming Pool\",\"Kitchen\"]', 'uploads/1783774299_4003_penang.webp', 'Paid', 'Published', 85, NULL, 0, 0.00, '2026-07-11 12:51:39', NULL, NULL, 'Whole House', 1, 'uploads/1783774299_4003_penang.webp', '[\"uploads\\/1783774299_8408_hstay9.jpg\",\"uploads\\/1783774299_6614_hstay10.jpg\",\"uploads\\/1783774299_1304_hstay11.jpg\"]', 'Paid', 2000.00, NULL, 'uploads/1783774299_5208_1783749474_9108_ic.png', 'uploads/1783774299_1764_1783749474_1957_bil.png', 'uploads/1783774299_6755_1783749474_1957_bil.png', 'uploads/1783774299_5384_1783749474_1957_bil.png', 'uploads/1783774299_7467_1783749474_1957_bil.png', 'exclusive');

-- --------------------------------------------------------

--
-- Table structure for table `homestay_blocked_dates`
--

CREATE TABLE `homestay_blocked_dates` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `blocked_date` date NOT NULL,
  `owner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `homestay_reviews`
--

CREATE TABLE `homestay_reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `feedback_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_hidden` tinyint(1) DEFAULT 0,
  `admin_reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homestay_reviews`
--

INSERT INTO `homestay_reviews` (`id`, `booking_id`, `homestay_id`, `user_id`, `rating`, `feedback_text`, `created_at`, `is_hidden`, `admin_reply`) VALUES
(1, 1, 11, 2, 5, 'ok', '2026-07-04 10:36:39', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `homestay_rooms`
--

CREATE TABLE `homestay_rooms` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `price_modifier` decimal(10,2) DEFAULT 0.00,
  `status` enum('Available','Booked') DEFAULT 'Available',
  `room_type` varchar(100) DEFAULT 'Double Room',
  `price_per_night` decimal(10,2) DEFAULT 150.00,
  `max_guests` int(11) DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homestay_rooms`
--

INSERT INTO `homestay_rooms` (`id`, `homestay_id`, `room_name`, `price_modifier`, `status`, `room_type`, `price_per_night`, `max_guests`) VALUES
(1, 12, 'Orchid', 0.00, 'Available', 'Single Room', 220.00, 2),
(2, 12, 'Rose', 0.00, 'Available', 'Double Room', 350.00, 4),
(3, 12, 'Hibiscus Room', 0.00, 'Available', 'Deluxe Studio', 400.00, 6),
(4, 13, 'Lavender Room', 0.00, 'Available', 'Single Room', 61.00, 2),
(5, 13, 'Daisy Room', 0.00, 'Available', 'Single Room', 61.00, 2),
(6, 13, 'Sunflower Room', 0.00, 'Available', 'Double Room', 80.00, 4),
(7, 17, 'Twin Room', 0.00, 'Available', 'Single Room', 120.00, 2),
(8, 17, 'Dekuxe Quadruple', 0.00, 'Available', 'Deluxe Studio', 200.00, 6),
(9, 17, 'Queen', 0.00, 'Available', 'Double Room', 180.00, 3),
(10, 19, 'Deluxe', 0.00, 'Available', 'Deluxe Studio', 120.00, 2),
(11, 19, 'Family', 0.00, 'Available', 'Family Suite', 180.00, 4),
(12, 21, 'Standard', 0.00, 'Available', 'Single Room', 100.00, 2),
(13, 21, 'Superior', 0.00, 'Available', 'Double Room', 150.00, 4),
(14, 23, 'Deluxe', 0.00, 'Available', 'Deluxe Studio', 120.00, 4),
(15, 23, 'Family', 0.00, 'Available', 'Family Suite', 180.00, 8);

-- --------------------------------------------------------

--
-- Table structure for table `host_settlements`
--

CREATE TABLE `host_settlements` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `host_name` varchar(255) NOT NULL,
  `booking_ids` text NOT NULL COMMENT 'Comma-separated booking IDs included',
  `total_gross` decimal(10,2) NOT NULL,
  `platform_cut` decimal(10,2) NOT NULL,
  `host_payout` decimal(10,2) NOT NULL,
  `installment_plan` tinyint(1) DEFAULT 0 COMMENT '0=full, 1=installment',
  `installments_total` int(11) DEFAULT 1,
  `installments_paid` int(11) DEFAULT 0,
  `status` enum('Pending','Partial','Settled') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `host_settlements`
--

INSERT INTO `host_settlements` (`id`, `host_id`, `host_name`, `booking_ids`, `total_gross`, `platform_cut`, `host_payout`, `installment_plan`, `installments_total`, `installments_paid`, `status`, `notes`, `created_at`) VALUES
(1, 5, 'Faiz', '2', 653.40, 65.34, 588.06, 0, 1, 1, 'Settled', '', '2026-07-07 17:22:44'),
(2, 5, 'Faiz', '4', 352.00, 35.20, 316.80, 0, 1, 0, 'Pending', '', '2026-07-08 01:23:16'),
(3, 5, 'Faiz', '1', 217.80, 21.78, 196.02, 1, 3, 3, 'Settled', '', '2026-07-08 01:23:27');

-- --------------------------------------------------------

--
-- Table structure for table `host_settlement_installments`
--

CREATE TABLE `host_settlement_installments` (
  `id` int(11) NOT NULL,
  `settlement_id` int(11) NOT NULL,
  `installment_no` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` datetime DEFAULT NULL,
  `status` enum('Pending','Paid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `host_settlement_installments`
--

INSERT INTO `host_settlement_installments` (`id`, `settlement_id`, `installment_no`, `amount`, `due_date`, `paid_date`, `status`) VALUES
(1, 1, 1, 588.06, '2026-07-07', '2026-07-08 09:16:39', 'Paid'),
(2, 2, 1, 316.80, '2026-07-08', NULL, 'Pending'),
(3, 3, 1, 66.65, '2026-07-08', '2026-07-11 14:16:54', 'Paid'),
(4, 3, 2, 64.69, '2026-08-08', '2026-07-11 14:16:54', 'Paid'),
(5, 3, 3, 64.68, '2026-09-08', '2026-07-11 14:16:54', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `owner_notifications`
--

CREATE TABLE `owner_notifications` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_notifications`
--

INSERT INTO `owner_notifications` (`id`, `owner_id`, `message`, `is_read`, `created_at`) VALUES
(1, 5, 'You have received a new booking reservation for \'Homestay Seri Tanjung\' (Booking #3).', 0, '2026-07-04 10:36:24'),
(2, 5, 'A new traveler review has been posted for \'Ipoh Homestay by IPOH TREATS PLT\' (Rating: 5/5 Stars).', 0, '2026-07-04 10:36:39'),
(3, 5, 'Congratulations! Your listing request for \'Ipoh Homestay by IPOH TREATS PLT\' has been approved and is now Published.', 0, '2026-07-04 10:52:17'),
(4, 5, 'You have received a new booking reservation for \'Homestay Seri Tanjung\' (Booking #4).', 0, '2026-07-07 11:09:39'),
(5, 5, 'You have received a new booking reservation for \'Ipoh Homestay by IPOH TREATS PLT\' (Booking #6).', 0, '2026-07-08 01:13:23'),
(6, 5, 'You have received a new booking reservation for \'Homestay Seri Tanjung\' (Booking #8).', 0, '2026-07-08 01:15:39'),
(7, 5, 'Your listing request for \'Homestay Kampung Paya Guring\' was rejected by Admin. Reason: tak cukup info', 0, '2026-07-08 01:24:18'),
(8, 7, 'Congratulations! Your listing request for \'Homestay Pekan Pahang Semi‑D\' has been approved and is now Published.', 1, '2026-07-11 06:02:46'),
(9, 7, 'You have received a new booking reservation for \'Homestay Pekan Pahang Semi‑D\' (Booking #10).', 1, '2026-07-11 06:04:06'),
(10, 7, 'You have received a new booking reservation for \'Homestay Pekan Pahang Semi‑D\' (Booking #11).', 1, '2026-07-11 06:32:49'),
(11, 7, 'You have received a new booking reservation for \'Homestay Pekan Pahang Semi‑D\' (Booking #12).', 1, '2026-07-11 06:37:42'),
(12, 7, 'You have received a new booking reservation for \'Homestay Pekan Pahang Semi‑D\' (Booking #15).', 1, '2026-07-11 11:06:33'),
(13, 10, 'Congratulations! Your listing request for \'Bayt Rizq Cameron Highlands\' has been approved and is now Published.', 0, '2026-07-11 11:29:25'),
(14, 10, 'Congratulations! Your listing request for \'Sri Chempaka Homestay\' has been approved and is now Published.', 0, '2026-07-11 11:29:27'),
(15, 7, 'Congratulations! Your listing request for \'G Residence, Menara B, Jalan Mutiara 7, Johor Bahru, Johor, Malaysia\' has been approved and is now Published.', 0, '2026-07-11 11:41:37'),
(16, 5, 'Congratulations! Your listing request for \'Home2828Stay\' has been approved and is now Published.', 0, '2026-07-11 11:52:14'),
(17, 5, 'Congratulations! Your listing request for \'D’Warisan Homestay Kuala Terengganu\' has been approved and is now Published.', 0, '2026-07-11 12:08:24'),
(18, 5, 'Congratulations! Your listing request for \'Taiping Lake View Homestay (Whole House)\' has been approved and is now Published.', 0, '2026-07-11 12:34:59'),
(19, 10, 'Congratulations! Your listing request for \'Sekeping Serendah\' has been approved and is now Published.', 0, '2026-07-11 12:35:00'),
(20, 10, 'Congratulations! Your listing request for \'Azlam Valley\' has been approved and is now Published.', 0, '2026-07-11 12:35:02'),
(21, 5, 'Congratulations! Your listing request for \'Kinabalu Mountain View Homestay Kundasang (Whole House)\' has been approved and is now Published.', 0, '2026-07-11 12:44:34'),
(22, 5, 'You have received a new booking reservation for \'Kinabalu Mountain View Homestay Kundasang (Whole House)\' (Booking #16).', 0, '2026-07-11 12:45:30'),
(23, 10, 'Congratulations! Your listing request for \'Seaview Homestay Batu Ferringhi (Whole House)\' has been approved and is now Published.', 0, '2026-07-11 12:52:10'),
(24, 10, 'You have received a new booking reservation for \'Sekeping Serendah\' (Booking #17).', 0, '2026-07-11 12:54:08');

-- --------------------------------------------------------

--
-- Table structure for table `owner_reports`
--

CREATE TABLE `owner_reports` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `report_month` varchar(20) NOT NULL,
  `bookings_count` int(11) DEFAULT 0,
  `completed_count` int(11) DEFAULT 0,
  `cancelled_count` int(11) DEFAULT 0,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  `commission` decimal(10,2) DEFAULT 0.00,
  `earnings` decimal(10,2) DEFAULT 0.00,
  `avg_rating` decimal(3,2) DEFAULT 0.00,
  `reviews_count` int(11) DEFAULT 0,
  `popular_homestay` varchar(255) DEFAULT '',
  `occupancy_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_reports`
--

INSERT INTO `owner_reports` (`id`, `owner_id`, `report_month`, `bookings_count`, `completed_count`, `cancelled_count`, `total_revenue`, `commission`, `earnings`, `avg_rating`, `reviews_count`, `popular_homestay`, `occupancy_rate`, `created_at`) VALUES
(1, 5, 'July 2026', 0, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0, 'Kinabalu Mountain View Homestay Kundasang (Whole House)', 0.00, '2026-07-04 11:02:55'),
(2, 7, 'July 2026', 7, 0, 0, 1309.00, 130.90, 1178.10, 0.00, 0, 'Homestay Pekan Pahang Semi‑D', 11.29, '2026-07-11 05:58:04'),
(3, 10, 'July 2026', 0, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0, 'Sri Chempaka Homestay', 0.00, '2026-07-11 11:21:23');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `billcode` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Paid',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `user_id`, `homestay_id`, `billcode`, `amount`, `status`, `payment_date`) VALUES
(1, 5, 6, 'MOCKTP6', 29.00, 'Paid', '2026-06-28 14:53:28'),
(2, 5, 7, 'MOCKTP7', 29.00, 'Paid', '2026-06-28 14:57:45'),
(4, 5, 11, 'j7a6guoq', 29.00, 'Paid', '2026-06-28 15:45:23'),
(5, 5, 9, '4fnz7epw', 29.00, 'Paid', '2026-06-29 14:36:24'),
(6, 2, 11, 'g7pcl1xh', 0.00, 'Paid', '2026-07-01 06:58:19'),
(7, 2, 9, '5y5zu1g9', 0.00, 'Paid', '2026-07-01 07:01:29'),
(8, 2, 11, '1lvdfs2m', 217.80, 'Paid', '2026-07-01 07:06:52'),
(9, 5, 11, '8n6grf1v', 29.00, 'Paid', '2026-07-02 01:27:11'),
(10, 2, 9, '5ulfvdhd', 352.00, 'Paid', '2026-07-04 10:36:24'),
(11, 2, 9, '9kh1nms6', 352.00, 'Paid', '2026-07-07 11:09:39'),
(12, 2, 11, 'iox8o7xf', 435.60, 'Paid', '2026-07-08 01:13:23'),
(13, 2, 9, 'ao1uj1kq', 352.00, 'Paid', '2026-07-08 01:15:39'),
(14, 7, 12, 'MOCKTP12', 29.00, 'Paid', '2026-07-11 05:58:10'),
(15, 2, 12, 'he6mjurv', 242.00, 'Paid', '2026-07-11 06:04:06'),
(16, 2, 12, 'hbbbhco4', 440.00, 'Paid', '2026-07-11 06:32:49'),
(17, 2, 12, 'm8frvak6', 242.00, 'Paid', '2026-07-11 06:37:42'),
(18, 2, 12, 'vnbwfdyx', 385.00, 'Paid', '2026-07-11 11:06:33'),
(19, 10, 13, 'vaoazrch', 29.00, 'Paid', '2026-07-11 11:21:23'),
(20, 10, 14, '6i1vf4fm', 29.00, 'Paid', '2026-07-11 11:28:42'),
(21, 7, 16, '87cis2cm', 29.00, 'Paid', '2026-07-11 11:39:43'),
(22, 5, 17, 'MOCKTP17', 29.00, 'Paid', '2026-07-11 11:49:56'),
(23, 5, 18, '1m6v9ywg', 29.00, 'Paid', '2026-07-11 12:07:52'),
(24, 7, 19, '4wjvrx2o', 29.00, 'Paid', '2026-07-11 12:11:53'),
(25, 5, 20, '4jzdnqlu', 29.00, 'Paid', '2026-07-11 12:20:12'),
(26, 10, 21, '50hohwxh', 29.00, 'Paid', '2026-07-11 12:29:22'),
(27, 10, 22, 'fwodb2zx', 29.00, 'Paid', '2026-07-11 12:33:25'),
(28, 5, 23, 'u765divf', 29.00, 'Paid', '2026-07-11 12:43:46'),
(29, 11, 23, 'c6htvym4', 396.00, 'Paid', '2026-07-11 12:45:30'),
(30, 10, 24, '49ghss0y', 29.00, 'Paid', '2026-07-11 12:51:51'),
(31, 11, 22, 'v4ys90rl', 308.00, 'Paid', '2026-07-11 12:54:08');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'system_name', 'Voyago');

-- --------------------------------------------------------

--
-- Table structure for table `travel_memories`
--

CREATE TABLE `travel_memories` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tripmates`
--

CREATE TABLE `tripmates` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Joined'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_budget` decimal(10,2) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `status` enum('Active','Completed') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `finished_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trip_attachments`
--

CREATE TABLE `trip_attachments` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_category` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_attachments`
--

INSERT INTO `trip_attachments` (`id`, `homestay_id`, `user_id`, `file_name`, `file_category`, `file_path`, `upload_date`) VALUES
(1, 11, 2, 'Booking_Receipt_1lvdfs2m.pdf', 'Reservation', '#', '2026-07-01 07:06:52'),
(2, 9, 2, 'Booking_Receipt_5ulfvdhd.pdf', 'Reservation', '#', '2026-07-04 10:36:24');

-- --------------------------------------------------------

--
-- Table structure for table `trip_documents`
--

CREATE TABLE `trip_documents` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_documents`
--

INSERT INTO `trip_documents` (`id`, `homestay_id`, `doc_type`, `file_path`, `created_at`) VALUES
(1, 99, 'Flight Ticket', 'uploads/docs/1783170505_about us 1.jpeg', '2026-07-04 13:08:25'),
(2, 4, 'Booking Receipt', 'download_receipt.php?booking_id=4', '2026-07-07 11:09:39'),
(3, 6, 'Booking Receipt', 'download_receipt.php?booking_id=6', '2026-07-08 01:13:23'),
(4, 8, 'Booking Receipt', 'download_receipt.php?booking_id=8', '2026-07-08 01:15:39'),
(5, 10, 'Booking Receipt', 'download_receipt.php?booking_id=10', '2026-07-11 06:04:06'),
(6, 11, 'Booking Receipt', 'download_receipt.php?booking_id=11', '2026-07-11 06:32:49'),
(7, 12, 'Booking Receipt', 'download_receipt.php?booking_id=12', '2026-07-11 06:37:42'),
(8, 15, 'Booking Receipt', 'download_receipt.php?booking_id=15', '2026-07-11 11:06:33'),
(9, 16, 'Booking Receipt', 'download_receipt.php?booking_id=16', '2026-07-11 12:45:30'),
(10, 16, 'Flight Ticket', 'uploads/docs/1783774003_tiket flight.jpeg', '2026-07-11 12:46:43'),
(11, 17, 'Booking Receipt', 'download_receipt.php?booking_id=17', '2026-07-11 12:54:08');

-- --------------------------------------------------------

--
-- Table structure for table `trip_expenses`
--

CREATE TABLE `trip_expenses` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `payer` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_expenses`
--

INSERT INTO `trip_expenses` (`id`, `homestay_id`, `title`, `amount`, `category`, `payer`, `created_at`) VALUES
(1, 4, 'Stay Booking Payment', 352.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-07 11:09:39'),
(2, 6, 'Stay Booking Payment', 435.60, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-08 01:13:23'),
(3, 8, 'Stay Booking Payment', 352.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-08 01:15:39'),
(4, 10, 'Stay Booking Payment', 242.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-11 06:04:06'),
(5, 11, 'Stay Booking Payment', 440.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-11 06:32:49'),
(6, 12, 'Stay Booking Payment', 242.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-11 06:37:42'),
(7, 15, 'Stay Booking Payment', 385.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-11 11:06:33'),
(8, 16, 'Stay Booking Payment', 396.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-11 12:45:30'),
(9, 17, 'Stay Booking Payment', 308.00, 'Accommodation', 'Online Banking (ToyyibPay)', '2026-07-11 12:54:08');

-- --------------------------------------------------------

--
-- Table structure for table `trip_mates`
--

CREATE TABLE `trip_mates` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `debt_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_mates`
--

INSERT INTO `trip_mates` (`id`, `homestay_id`, `name`, `debt_amount`, `created_at`) VALUES
(1, 99, 'faiz', 20.00, '2026-07-04 13:08:39'),
(2, 16, 'Selina', 180.00, '2026-07-11 12:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `trip_memories`
--

CREATE TABLE `trip_memories` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `caption` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_memories`
--

INSERT INTO `trip_memories` (`id`, `homestay_id`, `file_path`, `caption`, `created_at`) VALUES
(1, 16, 'uploads/memories/1783774047_hstay sabah.webp', 'Homestay View', '2026-07-11 12:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Traveller/User','Local Homestay Owner') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `phone`, `address`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@voyago.com', '0123456789', 'Voyago HQ, Machang', '$2y$10$7heNx7giIAWZ1DQOwzJtAebxWBMuEriV96VBHevmTbFEcw1EFFcZC', 'Admin', '2026-06-28 07:18:21'),
(2, 'deena', 'deena@voyago.com', '0172645813', 'NO.20, JALAN DAHLIA 1 , TAMAN PUCHONG INDAH', '$2y$10$llRu77.M7O9ARq8InDPzl.AVuQugmMqFOaRr4ovQlgkUagyxZHf.W', 'Traveller/User', '2026-06-28 07:30:39'),
(4, 'Aiman', 'aiman@homestay.com', '0126601790', 'Perak', '$2y$10$Y3usSinhR7qSifUF.Z.6cOwebt9Ifpmn2JHM40m/bl7wehQA4ynj6', 'Local Homestay Owner', '2026-06-28 11:11:44'),
(5, 'Faiz', 'faiz@gmail.com', '012345678', 'puchong', '$2y$10$jlhRxW3SLf3vIxas05mK5uqvQCT/d/R74SFz.3Rp7XNdXDKqNCi6K', 'Local Homestay Owner', '2026-06-28 11:46:27'),
(7, 'Alisha', 'aimisha47@gmail.com', '0199476198', '149, JALAN TAMAN ZASNA, 15350 KOTA BHARU, KELANTAN', '$2y$10$KnFZ8xSnMhPu31e93y3BouQBBRCk4eypouIROypDZdTIqgfcq8j2a', 'Local Homestay Owner', '2026-07-04 11:37:48'),
(10, 'Rahmansah Bin Sawal', 'rahman660@gmail.com', '0126601790', '41 , Jalan Tanjung Muda , Kunci Air Buang , Tanjung Karang , 41500, Selangor', '$2y$10$gjnsKhes6O1vyHtcT26NlupE3JpD7vrmkLHI9zaZWfqD/Q4ZY2Dbe', 'Local Homestay Owner', '2026-07-11 10:50:23'),
(11, 'Arina Nadhirah', 'arina123@gmail.com', '0147981267', '11, Kondomium Cheras , Kuala Lumpur', '$2y$10$9XmlmUMp2m8yGgmzQtJaeeUzPkuaHaIESYFyLvCSEPNhtyFQpicCC', 'Traveller/User', '2026-07-11 10:54:38');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attractions`
--
ALTER TABLE `attractions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_installments`
--
ALTER TABLE `booking_installments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_installment` (`booking_id`,`installment_no`);

--
-- Indexes for table `homestays`
--
ALTER TABLE `homestays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `homestay_blocked_dates`
--
ALTER TABLE `homestay_blocked_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_block` (`homestay_id`,`blocked_date`);

--
-- Indexes for table `homestay_reviews`
--
ALTER TABLE `homestay_reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homestay_rooms`
--
ALTER TABLE `homestay_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `host_settlements`
--
ALTER TABLE `host_settlements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `host_settlement_installments`
--
ALTER TABLE `host_settlement_installments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_settlement_inst` (`settlement_id`,`installment_no`);

--
-- Indexes for table `owner_notifications`
--
ALTER TABLE `owner_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `owner_reports`
--
ALTER TABLE `owner_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `travel_memories`
--
ALTER TABLE `travel_memories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tripmates`
--
ALTER TABLE `tripmates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_attachments`
--
ALTER TABLE `trip_attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_documents`
--
ALTER TABLE `trip_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_expenses`
--
ALTER TABLE `trip_expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_mates`
--
ALTER TABLE `trip_mates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_memories`
--
ALTER TABLE `trip_memories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_homestay` (`user_id`,`homestay_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attractions`
--
ALTER TABLE `attractions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `booking_installments`
--
ALTER TABLE `booking_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `homestays`
--
ALTER TABLE `homestays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `homestay_blocked_dates`
--
ALTER TABLE `homestay_blocked_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homestay_reviews`
--
ALTER TABLE `homestay_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homestay_rooms`
--
ALTER TABLE `homestay_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `host_settlements`
--
ALTER TABLE `host_settlements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `host_settlement_installments`
--
ALTER TABLE `host_settlement_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `owner_notifications`
--
ALTER TABLE `owner_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `owner_reports`
--
ALTER TABLE `owner_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `travel_memories`
--
ALTER TABLE `travel_memories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tripmates`
--
ALTER TABLE `tripmates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trip_attachments`
--
ALTER TABLE `trip_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trip_documents`
--
ALTER TABLE `trip_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `trip_expenses`
--
ALTER TABLE `trip_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `trip_mates`
--
ALTER TABLE `trip_mates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trip_memories`
--
ALTER TABLE `trip_memories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `homestays`
--
ALTER TABLE `homestays`
  ADD CONSTRAINT `homestays_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
