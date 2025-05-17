<?php

include "connect.php"; 
include '../../includes/connection.php';

if (!isset($_SESSION['Did'])) {
    header("Location: deliverylogin.php");
    exit();
}

$id = $_SESSION['Did'];
$name = $_SESSION['name'];

// Check if order_id is given
if (!isset($_GET['order_id'])) {
    die("Order ID not specified.");
}

$order_id = (int)$_GET['order_id'];

// Fetch order info including location and address
$sql = "SELECT location, address FROM food_donations WHERE Fid = $order_id AND (delivery_by = $id OR delivery_by IS NULL) LIMIT 1";
$result = mysqli_query($connection, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Order not found or you don't have permission to view it.");
}

$order = mysqli_fetch_assoc($result);

// For demonstration, let's assume delivery address is fixed (can be your warehouse or restaurant address)
$delivery_address = "Chennai Institue of technology, Kundrathur";

// We need to get lat/lng of pickup and delivery locations.
// To do that, we use Google Maps Geocoding API in JS below.

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Route Map</title>
    <style>
        #map {
            height: 600px;
            width: 100%;
            margin: 20px auto;
            max-width: 1000px;
            border: 1px solid #ccc;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #fafafa;
        }
        h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        .info {
            max-width: 1000px;
            margin: 0 auto 10px auto;
            background: #fff;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 0 5px #ccc;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Plates<b style="color: #06C167;">4All</b></div>
    <nav class="nav-bar">
        <ul>
            <li><a href="delivery.php">Home</a></li>
            <li><a href="openmap.php?order_id=<?php echo $order_id; ?>" class="active">Open Map</a></li>
            <li><a href="deliverymyord.php">My Orders</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<h2>Route for Order #<?php echo $order_id; ?></h2>

<div class="info">
    <strong>Pickup Location:</strong> <?php echo htmlspecialchars($order['address']); ?><br/>
    <strong>Delivery Location:</strong> <?php echo htmlspecialchars($delivery_address); ?>
</div>

<div id="map"></div>

<script>
// Google Maps API key - replace with your own key
const API_KEY = "AIzaSyBeZYHU38BSR8XOeYXrXPL4smJgWzJQZPg";

// Locations from PHP
const pickupAddress = "<?php echo addslashes($order['address']); ?>";
const deliveryAddress = "<?php echo addslashes($delivery_address); ?>";

let map, directionsService, directionsRenderer;

function initMap() {
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();

    // Center map roughly at pickup location initially
    const geocoder = new google.maps.Geocoder();

    geocoder.geocode({ address: pickupAddress }, (results, status) => {
        if (status === 'OK' && results[0]) {
            const center = results[0].geometry.location;
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: center,
            });
            directionsRenderer.setMap(map);

            calculateAndDisplayRoute();
        } else {
            alert('Failed to locate pickup address on map: ' + status);
        }
    });
}

function calculateAndDisplayRoute() {
    directionsService.route(
        {
            origin: pickupAddress,
            destination: deliveryAddress,
            travelMode: google.maps.TravelMode.DRIVING,
        },
        (response, status) => {
            if (status === "OK") {
                directionsRenderer.setDirections(response);
            } else {
                alert("Directions request failed due to " + status);
            }
        }
    );
}
</script>

<script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBeZYHU38BSR8XOeYXrXPL4smJgWzJQZPg&callback=initMap">
</script>

</body>
</html>
