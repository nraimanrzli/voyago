 <nav>
  <div class="nav__header">
    <div class="nav__logo">
      <a href="dashboard.php" class="logo"><?= htmlspecialchars($system_name) ?><span>.</span></a>
    </div>
    <div class="nav__menu__btn" id="menu-btn">
      <i class="ri-menu-line"></i>
    </div>
  </div>

  <ul class="nav__links" id="nav-links">
    <li><a href="dashboard.php">HOME</a></li>
    <li><a href="smart-planner.php">SMART PLANNER</a></li>
    <li><a href="booking.php">BOOKING</a></li>
    <li><a href="budget.php">SMART CALCULATOR</a></li>
     <li><a href="index.php?action=logout">LOG OUT</a></li>
    <li class="nav__mobile__btn"><a href="smart-planner.php">START NOW</a></li>
 </ul>
 
 <div class="nav__btns">
    <button class="btn" onclick="window.location.href='smart-planner.php'">PLAN NOW</button>
  </div>
</nav>