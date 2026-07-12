<?php
// dashboard.php - Traveller Dashboard
require_once 'toyyibpay_config.php';

// Verify session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$fullname = isset($_SESSION['user_fullname']) ? $_SESSION['user_fullname'] : 'Traveller';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title><?= htmlspecialchars($system_name) ?> | Explore Malaysia</title>
    <style>
      a.btn {
        text-decoration: none;
        display: inline-block;
        text-align: center;
      }
      .badge-status.upcoming { background: #dbeafe !important; color: #1e40af !important; }
      .badge-status.checked-in { background: #fef3c7 !important; color: #92400e !important; }
      .badge-status.completed { background: #d1fae5 !important; color: #065f46 !important; }
      .badge-status.reviewed { background: #d1fae5 !important; color: #065f46 !important; }
      .badge-status.pending { background: #fee2e2 !important; color: #991b1b !important; }
      .badge-payment.paid { background: #d1fae5 !important; color: #065f46 !important; }
      .badge-payment.awaiting-payment { background: #fee2e2 !important; color: #991b1b !important; }
    </style>
  </head>
  <body class="dark-modern-theme">
    
    <?php include('bar.php'); ?>
    

    <header id="home">
      <div class="header__container">
        <div class="header__content">
          <p>ELEVATE YOUR TRAVEL JOURNEY</p>
          <h1>Explore Malaysia, Curated by <?= htmlspecialchars($system_name) ?>!</h1>
          <div class="header__btns">
            <a href="smart-planner.php" class="btn">Plan your adventure</a>
            <a href="#">
              <span><i class="ri-play-circle-fill"></i></span>
            </a>
          </div>
        </div>
        <div class="header__image">
          <img src="images/header.png" alt="header" />
        </div>
      </div>
    </header>

    <section class="section__container destination__container" id="about">
      <h2 class="section__header">Discover the Hidden Gems</h2>
      <p class="section__description">
        Unlock the hidden magic of travel with our expertly curated destinations, where every location is a gateway to unforgettable experiences and cherished memories.
      </p>
      <div class="destination__grid">
        <div class="destination__card">
          <img src="images/destination-1.jpg" alt="destination" />
          <div class="destination__card__details">
            <div>
              <h4>Sarawak</h4>
              <p>Pa Ramapuh Waterfall City, Malaysia</p>
            </div>
            <div class="destination__rating">
              <span><i class="ri-star-fill"></i></span>
              4.7
            </div>
          </div>
        </div>
        <div class="destination__card">
          <img src="images/destination-2.jpg" alt="destination" />
          <div class="destination__card__details">
            <div>
              <h4>Perak</h4>
              <p>Kuala Sepetang Forest</p>
            </div>
            <div class="destination__rating">
              <span><i class="ri-star-fill"></i></span>
              4.5
            </div>
          </div>
        </div>
        <div class="destination__card">
          <img src="images/destination-3.jpg" alt="destination" />
          <div class="destination__card__details">
            <div>
              <h4>Selangor</h4>
              <p>Kampung Kuantan Fireflies Park, Malaysia</p>
            </div>
            <div class="destination__rating">
              <span><i class="ri-star-fill"></i></span>
              4.8
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section__container journey__container" id="tour">
      <h2 class="section__header">Journey To The Hidden Gems Made Simple!</h2>
      <p class="section__description">
        Effortless Planning for Your Next Adventure
      </p>
      <div class="journey__grid">
        <div class="journey__card">
          <div class="journey__card__bg">
            <span><i class="ri-bookmark-3-line"></i></span>
            <h4>Seamless Booking Process</h4>
          </div>
          <div class="journey__card__content">
            <span><i class="ri-bookmark-3-line"></i></span>
            <h4>Easy Reservations, One Click Away</h4>
            <p>
              From flights and accommodations to activities and transfers,
              everything you need is available at your fingertips, making travel
              planning effortless.
            </p>
          </div>
        </div>
        <div class="journey__card">
          <div class="journey__card__bg">
            <span><i class="ri-landscape-fill"></i></span>
            <h4>Tailored Itineraries</h4>
          </div>
          <div class="journey__card__content">
            <span><i class="ri-landscape-fill"></i></span>
            <h4>Customized Plans Just for You</h4>
            <p>
              Enjoy personalized travel plans designed to match your preferences
              and interests. Whether you seek adventure or cultural immersion,
              our tailored itineraries ensure your journey is uniquely yours.
            </p>
          </div>
        </div>
        <div class="journey__card">
          <div class="journey__card__bg">
            <span><i class="ri-map-2-line"></i></span>
            <h4>Expert Local Insights</h4>
          </div>
          <div class="journey__card__content">
            <span><i class="ri-map-2-line"></i></span>
            <h4>Insider Tips and Recommendations</h4>
            <p>
              We provide curated recommendations for dining, sightseeing, and
              hidden gems, so you can experience each destination like a local.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="section__container showcase__container" id="package">
      <div class="showcase__image">
        <img src="images/showcase.jpg" alt="showcase" />
      </div>
      <div class="showcase__content">
        <h4>UNLEASH WANDERLUST WITH <?= htmlspecialchars($system_name) ?></h4>
        <p>
          Embark on a journey like no other with <?= htmlspecialchars($system_name) ?>, where your travel
          dreams come to life. Our mission is to inspire and facilitate your
          adventures, whether you seek the vibrant energy of bustling
          cityscapes, the serene beauty of pristine beaches, or the captivating
          history of ancient landmarks. At <?= htmlspecialchars($system_name) ?>, we provide expertly curated
          destinations and personalized itineraries, ensuring that every trip is
          tailored to your unique preferences. Discover hidden gems, immerse
          yourself in diverse cultures, and create unforgettable memories that
          will last a lifetime.
        </p>
        <p>
          With <?= htmlspecialchars($system_name) ?> as your ultimate travel companion, exploring the wonders
          of the world has never been easier. Our insider tips and local
          insights give you the tools to navigate new places with confidence and
          excitement. From the moment you start planning to the day you return
          home, we are dedicated to making your travel experience seamless and
          enriching.
        </p>
        <div class="showcase__btn">
          <a href="smart-planner.php" class="btn">
            Start your adventure now
            <span><i class="ri-arrow-right-line"></i></span>
          </a>
        </div>
      </div>
    </section>

    <section class="section__container banner__container">
      <div class="banner__card">
        <h4>10+</h4>
        <p>Years Experience</p>
      </div>
      <div class="banner__card">
        <h4>12K</h4>
        <p>Happy Clients</p>
      </div>
      <div class="banner__card">
        <h4>4.8</h4>
        <p>Overall Ratings</p>
      </div>
    </section>

    <section class="section__container discover__container">
      <h2 class="section__header">Discover The World From Above</h2>
      <p class="section__description">
        Experience Breathtaking Views and Unique Perspectives
      </p>
      <div class="discover__grid">
        <div class="discover__card">
          <span><i class="ri-camera-lens-line"></i></span>
          <h4>Aerial Cityscapes</h4>
          <p>
            Witness the architectural marvels and bustling streets from
            bird's-eye view, offering a unique perspective.
          </p>
        </div>
        <div class="discover__card">
          <span><i class="ri-ship-line"></i></span>
          <h4>Coastal Wonders</h4>
          <p>
            Fly over pristine coastlines and turquoise waters, revealing hidden
            coves and vibrant coral reefs.
          </p>
        </div>
        <div class="discover__card">
          <span><i class="ri-landscape-line"></i></span>
          <h4>Historic Landmarks</h4>
          <p>
            Observe the grandeur of ancient castles and other significant sites
            in a way that ground tours can't offer.
          </p>
        </div>
      </div>
    </section>

    <section class="section__container client__container">
      <h2 class="section__header">Loved By Over Thousand Travelers</h2>
      <p class="section__description">
        Discover the stories of wanderlust and cherished memories through the
        eyes of our valued clients.
      </p>
      <div class="swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <div class="client__card">
              <div class="client__content">
                <div class="client__rating">
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                </div>
                <p>
                  <?= htmlspecialchars($system_name) ?> has completely transformed my travel experience. From
                  finding hidden gems in bustling cities to discovering serene
                  retreats off the beaten path, every detail was thoughtfully
                  arranged. I can't recommend <?= htmlspecialchars($system_name) ?> enough for anyone looking
                  to elevate their travel experience!
                </p>
              </div>
              <div class="client__details">
                <img src="images/client-1.jpg" alt="client" />
                <div>
                  <h4>Yuna</h4>
                  <h5>Travel Blogger</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="client__card">
              <div class="client__content">
                <div class="client__rating">
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                </div>
                <p>
                  My recent adventure with <?= htmlspecialchars($system_name) ?> was nothing short of
                  spectacular. The personalized itineraries and recommendations
                  they provided led me to extraordinary locations that I would
                  never have found on my own. I'm already planning my next
                  adventure with them!
                </p>
              </div>
              <div class="client__details">
                <img src="images/client-2.jpg" alt="client" />
                <div>
                  <h4>Idris Zafran</h4>
                  <h5>Adventure Enthusiast</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="client__card">
              <div class="client__content">
                <div class="client__rating">
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                </div>
                <p>
                  <?= htmlspecialchars($system_name) ?> offered a transformative experience for my research
                  into historical landmarks. The unique aerial perspectives and
                  provided a new level of appreciation and insight into the
                  sites I studied. I highly recommend their services to fellow
                  historians and cultural enthusiasts.
                </p>
              </div>
              <div class="client__details">
                <img src="images/client-3.jpg" alt="client" />
                <div>
                  <h4>Danish Noah</h4>
                  <h5>Cultural Historian</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="swiper-slide">
            <div class="client__card">
              <div class="client__content">
                <div class="client__rating">
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                  <span><i class="ri-star-fill"></i></span>
                </div>
                <p>
                  Finding a balance between work and travel can be challenging,
                  but <?= htmlspecialchars($system_name) ?> made it effortless. Their efficient planning and
                  excellent recommendations helped me maximize my downtime and
                  enjoy every moment of my trip. I look forward to working with
                  them again on future travels.
                </p>
              </div>
              <div class="client__details">
                <img src="images/client-4.jpg" alt="client" />
                <div>
                  <h4>Zaim Ahmad</h4>
                  <h5>Business Executive</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>



    <footer id="contact">
      <div class="section__container footer__container">
        <div class="footer__col">
          <div class="footer__logo">
            <a href="#" class="logo"><?= htmlspecialchars($system_name) ?></a>
          </div>
          <p>
            Explore the world with ease and excitement through our comprehensive
            travel platform. Your journey begins here, where seamless planning
            meets unforgettable experiences.
          </p>
          <ul class="footer__socials">
            <li>
              <a href="#"><i class="ri-facebook-fill"></i></a>
            </li>
            <li>
              <a href="#"><i class="ri-instagram-line"></i></a>
            </li>
            <li>
              <a href="#"><i class="ri-youtube-line"></i></a>
            </li>
          </ul>
        </div>
        <div class="footer__col">
          <h4>Quick Links</h4>
          <ul class="footer__links">
            <li><a href="#">Home</a></li>
            <li><a href="smart-planner.php">Smart Planner</a></li>
            <li><a href="#">Homestay/Hotel</a></li>
            <li><a href="#">Restaurant</a></li>
          </ul>
        </div>
        <div class="footer__col">
          <h4>Contact Us</h4>
          <ul class="footer__links">
            <li>
              <a href="#">
                <span><i class="ri-phone-fill"></i></span> +03-26564981
              </a>
            </li>
            <li>
              <a href="#">
                <span><i class="ri-record-mail-line"></i></span> info@<?= htmlspecialchars($system_name) ?>.official
              </a>
            </li>
            <li>
              <a href="#">
                <span><i class="ri-map-pin-2-fill"></i></span> Machang, Malaysia
              </a>
            </li>
          </ul>
        </div>
        <div class="footer__col">
          <h4>Subscribe</h4>
          <form action="/">
            <input type="text" placeholder="Enter your email" />
            <button class="btn">Subscribe</button>
          </form>
        </div>
      </div>
      <div class="footer__bar">
        Copyright © 2026 <?= htmlspecialchars($system_name) ?>. All rights reserved.
      </div>
    </footer>

    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
  </body>
</html>
