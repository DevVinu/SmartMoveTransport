<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Vehicle.php';
require_once __DIR__ . '/models/News.php';

$pageTitle = "SmartMove Transport | Professional Mobility";

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <?php include __DIR__ . '/components/hero.php'; ?>
  <?php include __DIR__ . '/components/explore.php'; ?>
  <?php include __DIR__ . '/components/steps.php'; ?>
  <?php include __DIR__ . '/components/savings.php'; ?>
  <?php include __DIR__ . '/components/earn.php'; ?>
  <?php include __DIR__ . '/components/about.php'; ?>
  <?php include __DIR__ . '/components/news.php'; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
