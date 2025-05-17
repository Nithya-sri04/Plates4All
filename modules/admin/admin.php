<?php
ob_start();
session_start();

include "connect.php"; 
include '../../includes/connection.php';

if (empty($_SESSION['name'])) {
    header("Location: signin.php");
    exit;
}

$success = '';
$error = '';

// Handle assignment form submission
if (isset($_POST['assign_order'])) {
    $order_id = $_POST['order_id'];
    $delivery_person_id = $_POST['delivery_person_id'];

    // Check if order already assigned
    $checkSql = "SELECT * FROM food_donations WHERE Fid = $order_id AND assigned_to IS NOT NULL";
    $checkResult = mysqli_query($connection, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Sorry, this order has already been assigned.";
    } else {
        $updateSql = "UPDATE food_donations SET assigned_to = $delivery_person_id WHERE Fid = $order_id";
        $updateResult = mysqli_query($connection, $updateSql);
        if ($updateResult) {
            $success = "Order assigned successfully.";
        } else {
            $error = "Error assigning order: " . mysqli_error($connection);
        }
    }
}

// Fetch unassigned orders for admin location
$loc = $_SESSION['location'];
$sql = "SELECT * FROM food_donations WHERE assigned_to IS NULL AND location = '$loc'";
$unassignedOrders = mysqli_query($connection, $sql);

// Fetch delivery persons
$deliverySql = "SELECT Did, name FROM delivery_persons WHERE city = '$loc'";
$deliveryPersons = mysqli_query($connection, $deliverySql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard Panel</title>
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
</head>
<body>
<nav>
    <div class="logo-name">
        <span class="logo_name">ADMIN</span>
    </div>
    <div class="menu-items">
        <ul class="nav-links">
            <li><a href="#"><i class="uil uil-estate"></i>Dashboard</a></li>
            <li><a href="analytics.php"><i class="uil uil-chart"></i> Analytics</a></li>
            <li><a href="donate.php"><i class="uil uil-heart"></i> Donates</a></li>
            <li><a href="feedback.php"><i class="uil uil-comments"></i> Feedbacks</a></li>
            <li><a href="adminprofile.php"><i class="uil uil-user"></i> Profile</a></li>
            <li><a href="#assign-orders-section"><i class="uil uil-check-circle"></i> Assign Orders</a></li>
        </ul>
        <ul class="logout-mode">
            <li><a href="logout.php"><i class="uil uil-signout"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<section class="dashboard">
    <div class="top">
        <i class="uil uil-bars sidebar-toggle"></i>
        <p class="logo">Plates4<b style="color: #06C167;">All</b></p>
    </div>

    <div class="dash-content">
        <div class="overview">
            <div class="title"><i class="uil uil-tachometer-fast-alt"></i> Dashboard</div>
            <div class="boxes">
                <div class="box box1">
                    <i class="uil uil-user"></i>
                    <span>Total users</span>
                    <?php
                        $query = "SELECT count(*) AS count FROM login";
                        $result = mysqli_query($connection, $query);
                        $row = mysqli_fetch_assoc($result);
                        echo "<span class='number'>{$row['count']}</span>";
                    ?>
                </div>
                <div class="box box2">
                    <i class="uil uil-comments"></i>
                    <span>Feedbacks</span>
                    <?php
                        $query = "SELECT count(*) AS count FROM user_feedback";
                        $result = mysqli_query($connection, $query);
                        $row = mysqli_fetch_assoc($result);
                        echo "<span class='number'>{$row['count']}</span>";
                    ?>
                </div>
                <div class="box box3">
                    <i class="uil uil-heart"></i>
                    <span>Total donates</span>
                    <?php
                        $query = "SELECT count(*) AS count FROM food_donations";
                        $result = mysqli_query($connection, $query);
                        $row = mysqli_fetch_assoc($result);
                        echo "<span class='number'>{$row['count']}</span>";
                    ?>
                </div>
            </div>
        </div>

        <div id="assign-orders-section" class="activity" style="margin-top: 40px;">
            <div class="title"><i class="uil uil-check-circle"></i> Assign Orders</div>

            <?php if (!empty($error)) echo "<p style='color: red; font-weight: bold;'>$error</p>"; ?>
            <?php if (!empty($success)) echo "<p style='color: green; font-weight: bold;'>$success</p>"; ?>

            <?php if (mysqli_num_rows($unassignedOrders) == 0) : ?>
                <p>No unassigned food donation orders in your location.</p>
            <?php else : ?>
                <form method="post">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Food</th>
                                <th>Category</th>
                                <th>Phone No</th>
                                <th>Date/Time</th>
                                <th>Address</th>
                                <th>Quantity</th>
                                <th>Assign To</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($order = mysqli_fetch_assoc($unassignedOrders)) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo htmlspecialchars($order['food']); ?></td>
                                <td><?php echo htmlspecialchars($order['category']); ?></td>
                                <td><?php echo htmlspecialchars($order['phoneno']); ?></td>
                                <td><?php echo htmlspecialchars($order['date']); ?></td>
                                <td><?php echo htmlspecialchars($order['address']); ?></td>
                                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                                <td>
                                    <select name="delivery_person_id" required>
                                        <option value="">Select</option>
                                        <?php
                                            mysqli_data_seek($deliveryPersons, 0);
                                            while ($dp = mysqli_fetch_assoc($deliveryPersons)) {
                                                echo '<option value="' . $dp['Did'] . '">' . htmlspecialchars($dp['name']) . '</option>';
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="order_id" value="<?php echo $order['Fid']; ?>">
                                    <button type="submit" name="assign_order">Assign</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="admin.js"></script>
</body>
</html>
