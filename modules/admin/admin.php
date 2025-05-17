<?php
ob_start(); 


include "connect.php"; 
include '../../includes/connection.php';

if($_SESSION['name']==''){
	header("location:signin.php");
}

// Handle assignment form submission
if (isset($_POST['assign_order'])) {
    $order_id = $_POST['order_id'];
    $delivery_person_id = $_POST['delivery_person_id'];

    // Check if order already assigned
    $checkSql = "SELECT * FROM food_donations WHERE Fid = $order_id AND assigned_to IS NOT NULL";
    $checkResult = mysqli_query($connection, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Sorry, this order has already been assigned to someone else.";
    } else {
        $updateSql = "UPDATE food_donations SET assigned_to = $delivery_person_id WHERE Fid = $order_id";
        $updateResult = mysqli_query($connection, $updateSql);
        if (!$updateResult) {
            $error = "Error assigning order: " . mysqli_error($connection);
        } else {
            $success = "Order assigned successfully.";
        }
    }
}

// Fetch unassigned orders for admin location
$loc= $_SESSION['location'];
$sql = "SELECT * FROM food_donations WHERE assigned_to IS NULL AND location='$loc'";
$unassignedOrders = mysqli_query($connection, $sql);

// Fetch delivery persons (replace 'delivery_persons' and fields as per your DB)
$deliverySql = "SELECT Did,name FROM delivery_persons WHERE city='$loc'";
$deliveryPersons = mysqli_query($connection, $deliverySql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    />
    <link rel="stylesheet" href="admin.css" />
    <link
      rel="stylesheet"
      href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"
    />
    <title>Admin Dashboard Panel</title>
</head>
<body>
<nav>
    <div class="logo-name">
        <div class="logo-image"></div>
        <span class="logo_name">ADMIN</span>
    </div>

    <div class="menu-items">
        <ul class="nav-links">
            <li><a href="#">
                <i class="uil uil-estate"></i>
                <span class="link-name">Dashboard</span>
            </a></li>
            <li><a href="analytics.php">
                <i class="uil uil-chart"></i>
                <span class="link-name">Analytics</span>
            </a></li>
            <li><a href="donate.php">
                <i class="uil uil-heart"></i>
                <span class="link-name">Donates</span>
            </a></li>
            <li><a href="feedback.php">
                <i class="uil uil-comments"></i>
                <span class="link-name">Feedbacks</span>
            </a></li>
            <li><a href="adminprofile.php">
                <i class="uil uil-user"></i>
                <span class="link-name">Profile</span>
            </a></li>
            <!-- New Assign Orders link -->
            <li><a href="#assign-orders-section">
                <i class="uil uil-check-circle"></i>
                <span class="link-name">Assign Orders</span>
            </a></li>
        </ul>

        <ul class="logout-mode">
            <li><a href="logout.php">
                <i class="uil uil-signout"></i>
                <span class="link-name">Logout</span>
            </a></li>
            <li class="mode">
                <a href="#">
                    <i class="uil uil-moon"></i>
                    <span class="link-name">Dark Mode</span>
                </a>
                <div class="mode-toggle"><span class="switch"></span></div>
            </li>
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
            <div class="title">
                <i class="uil uil-tachometer-fast-alt"></i>
                <span class="text">Dashboard</span>
            </div>

            <div class="boxes">
                <div class="box box1">
                    <i class="uil uil-user"></i>
                    <span class="text">Total users</span>
                    <?php
                        $query="SELECT count(*) as count FROM login";
                        $result=mysqli_query($connection, $query);
                        $row=mysqli_fetch_assoc($result);
                        echo "<span class=\"number\">".$row['count']."</span>";
                    ?>
                </div>
                <div class="box box2">
                    <i class="uil uil-comments"></i>
                    <span class="text">Feedbacks</span>
                    <?php
                        $query="SELECT count(*) as count FROM user_feedback";
                        $result=mysqli_query($connection, $query);
                        $row=mysqli_fetch_assoc($result);
                        echo "<span class=\"number\">".$row['count']."</span>";
                    ?>
                </div>
                <div class="box box3">
                    <i class="uil uil-heart"></i>
                    <span class="text">Total donates</span>
                    <?php
                        $query="SELECT count(*) as count FROM food_donations";
                        $result=mysqli_query($connection, $query);
                        $row=mysqli_fetch_assoc($result);
                        echo "<span class=\"number\">".$row['count']."</span>";
                    ?>
                </div>
            </div>
        </div>

        <!-- New Assign Orders Section -->
        <div id="assign-orders-section" class="activity" style="margin-top: 40px;">
            <div class="title">
                <i class="uil uil-check-circle"></i>
                <span class="text">Assign Orders</span>
            </div>

            <?php if (!empty($error)) : ?>
                <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (!empty($success)) : ?>
                <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <?php if (mysqli_num_rows($unassignedOrders) == 0) : ?>
                <p>No unassigned food donation orders in your location.</p>
            <?php else : ?>
                <form method="post" action="">
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
                                    <td data-label="Name"><?php echo htmlspecialchars($order['name']); ?></td>
                                    <td data-label="Food"><?php echo htmlspecialchars($order['food']); ?></td>
                                    <td data-label="Category"><?php echo htmlspecialchars($order['category']); ?></td>
                                    <td data-label="Phone No"><?php echo htmlspecialchars($order['phoneno']); ?></td>
                                    <td data-label="Date/Time"><?php echo htmlspecialchars($order['date']); ?></td>
                                    <td data-label="Address"><?php echo htmlspecialchars($order['address']); ?></td>
                                    <td data-label="Quantity"><?php echo htmlspecialchars($order['quantity']); ?></td>
                                    <td data-label="Assign To">
                                        <select name="delivery_person_id" required>
                                            <option value="">Select Delivery Person</option>
                                            <?php
                                                // Reset pointer for delivery persons query if needed
                                                mysqli_data_seek($deliveryPersons, 0);
                                                while ($dp = mysqli_fetch_assoc($deliveryPersons)) {
                                                    echo '<option value="' . $dp['id'] . '">' . htmlspecialchars($dp['name']) . '</option>';
                                                }
                                            ?>
                                        </select>
                                    </td>
                                    <td data-label="Action">
                                        <button type="submit" name="assign_order" value="<?php echo $order['Fid']; ?>" formaction="?order_id=<?php echo $order['Fid']; ?>">Assign</button>
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
