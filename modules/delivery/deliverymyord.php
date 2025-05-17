<?php
include "connect.php"; 
include '../../includes/connection.php';

if (!isset($_SESSION['Did'])) {
    header("Location: deliverylogin.php");
    exit();
}

$id = $_SESSION['Did'];
$name = $_SESSION['name'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['take_order'])) {
        $order_id = (int)$_POST['order_id'];

        $checkSql = "SELECT delivery_by FROM food_donations WHERE Fid = $order_id";
        $res = mysqli_query($connection, $checkSql);
        $row = mysqli_fetch_assoc($res);

        if ($row && $row['delivery_by'] === null) {
            $updateSql = "UPDATE food_donations SET delivery_by = $id, delivery_status = 'Taken' WHERE Fid = $order_id";
            mysqli_query($connection, $updateSql);

            // Redirect to openmap.php with order_id
            header("Location: openmap.php?order_id=" . $order_id);
            exit();
        } else {
            echo "<p style='color:red;'>Sorry, this order was already taken by someone else.</p>";
        }
    }

    if (isset($_POST['mark_delivered'])) {
        $order_id = (int)$_POST['order_id'];

        $checkSql = "SELECT delivery_by FROM food_donations WHERE Fid = $order_id";
        $res = mysqli_query($connection, $checkSql);
        $row = mysqli_fetch_assoc($res);

        if ($row && $row['delivery_by'] == $id) {
            $updateSql = "UPDATE food_donations SET delivery_status = 'Delivered' WHERE Fid = $order_id";
            mysqli_query($connection, $updateSql);
        } else {
            echo "<p style='color:red;'>You cannot update this order.</p>";
        }
    }
}

// Fetch orders
$sql = "SELECT Fid, name, phoneno, date, address AS pickup_address, location, delivery_by, delivery_status 
        FROM food_donations 
        WHERE delivery_by = $id OR delivery_by IS NULL
        ORDER BY date DESC";

$result = mysqli_query($connection, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Orders</title>
    <link rel="stylesheet" href="delivery.css" />
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; }
        th { background-color: #f4f4f4; }
        .btn { padding: 6px 12px; cursor: pointer; }
        .btn-take { background-color: #06C167; color: white; border: none; }
        .btn-delivered { background-color: #666; color: white; border: none; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; }
        .badge-pending { background-color: orange; }
        .badge-taken { background-color: #06C167; }
        .badge-delivered { background-color: #666; }
    </style>
</head>
<body>

<header>
    <div class="logo">Plates<b style="color: #06C167;">4All</b></div>
    <nav class="nav-bar">
        <ul>
            <li><a href="delivery.php">Home</a></li>
            <li><a href="openmap.php">Open Map</a></li>
            <li><a href="deliverymyord.php" class="active">My Orders</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<h2 style="text-align:center;">My Orders - <?php echo htmlspecialchars($name); ?></h2>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Phone No</th>
            <th>Date/Time</th>
            <th>Pickup Address</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phoneno']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['pickup_address']); ?></td>
                    <td>
                        <?php 
                            $status = $row['delivery_status'] ?? 'Pending';
                            if ($status === 'Pending') {
                                echo '<span class="badge badge-pending">Pending</span>';
                            } else if ($status === 'Taken') {
                                echo '<span class="badge badge-taken">Taken</span>';
                            } else if ($status === 'Delivered') {
                                echo '<span class="badge badge-delivered">Delivered</span>';
                            } else {
                                echo htmlspecialchars($status);
                            }
                        ?>
                    </td>
                    <td>
                        <?php if ($row['delivery_by'] === null): ?>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="order_id" value="<?php echo $row['Fid']; ?>">
                                <button type="submit" name="take_order" class="btn btn-take">Take Order</button>
                            </form>
                        <?php elseif ($row['delivery_by'] == $id && $status !== 'Delivered'): ?>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="order_id" value="<?php echo $row['Fid']; ?>">
                                <button type="submit" name="mark_delivered" class="btn btn-delivered">Mark as Delivered</button>
                            </form>
                        <?php elseif ($row['delivery_by'] == $id && $status === 'Delivered'): ?>
                            Delivered
                        <?php else: ?>
                            Assigned to another delivery person
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No orders found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
