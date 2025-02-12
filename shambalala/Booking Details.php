<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
	 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <link rel="icon" href="images/fevicon.jpg" type="image/gif" />
    <link rel="stylesheet" href="css/style.css">
	 <link rel="stylesheet" href="Booking Details.css">
	 
</head>
<body>
<h1>Booking Details</h1>
<div class="container">
  <form method="get" action="booking_details.php">
    <label for="search">Search:</label>
    <input type="text" name="search" id="search">
    <select name="search_by">
      <option value="id">Booking ID</option>
      <option value="email">Email</option>
      <option value="checkin_date">Check-in Date</option>
      <option value="room_type">Room Type</option>
      <option value="special_requests">Special Requests</option>
    </select>

    <!-- Filter by Check-in Date -->
    <label for="checkin_start_date">Check-in Start Date:</label>
    <input type="date" name="checkin_start_date" id="checkin_start_date">
    <label for="num_days">Number of Days:</label>
    <input type="number" name="num_days" id="num_days">

    <!-- Filter by Room Type -->
    <label for="room_type">Room Type:</label>
    <select name="room_type" id="room_type">
      <option value="">All</option>
      <option value="Single">Single</option>
      <option value="Double">Double</option>
      <option value="Suite">Suite</option>
    </select>

    <button type="submit">Search</button>
  </form>
  <br>
  <table id="bookings-table">
    <thead>
      <tr>
        <th data-sort="id">Booking ID</th>
        <th data-sort="room_type">Room Type</th>
        <th data-sort="num_rooms">Number of Rooms</th>
        <th data-sort="checkin_date">Check-in Date</th>
        <th data-sort="num_days">Number of Days</th>
        <th data-sort="special_requests">Special Requests</th>
        <th data-sort="total_price">Total Price</th>
        <th>Receipt Screenshot</th>
        <th data-sort="email">Email</th>
        <th data-sort="action">Delete Booking</th>
        <th data-sort="action">Confirm Booking</th>
        <th data-sort="action">Cancel Booking</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Establish database connection
      $server = "localhost";
      $dbUsername = "root";
      $password = "";
      $dbName = "contat";

      $mysqli = new mysqli($server, $dbUsername, $password, $dbName);
      if ($mysqli->connect_errno) {
          die("Failed to connect to MySQL: " + $mysqli->connect_error);
      }

      // Retrieve booking details from database
      $selectQuery = "SELECT * FROM bookings";

      // Search functionality
      if (isset($_GET['search']) && !empty($_GET['search'])) {
          $searchQuery = mysqli_real_escape_string($mysqli, $_GET['search']);
          $searchBy = mysqli_real_escape_string($mysqli, $_GET['search_by']);

          // Construct search condition based on selected search field
          $searchCondition = "$searchBy LIKE '%$searchQuery%'";

          $selectQuery .= " WHERE $searchCondition";
      }

      // Filter by Check-in Date
      if (isset($_GET['checkin_start_date']) && !empty($_GET['checkin_start_date'])) {
          $checkinStartDate = mysqli_real_escape_string($mysqli, $_GET['checkin_start_date']);
          $selectQuery .= " AND checkin_date >= '$checkinStartDate'";
      }
      if (isset($_GET['num_days']) && !empty($_GET['num_days'])) {
          $numDays = mysqli_real_escape_string($mysqli, $_GET['num_days']);
          $selectQuery .= " AND num_days = '$numDays'";
      }

      // Filter by Room Type
      if (isset($_GET['room_type']) && !empty($_GET['room_type'])) {
          $roomType = mysqli_real_escape_string($mysqli, $_GET['room_type']);
          $selectQuery .= " AND room_type = '$roomType'";
      }

      // Sort functionality
      if (isset($_GET['sort']) && !empty($_GET['sort'])) {
          $sortColumn = mysqli_real_escape_string($mysqli, $_GET['sort']);
          $sortOrder = mysqli_real_escape_string($mysqli, $_GET['order']);
          $selectQuery .= " ORDER BY $sortColumn $sortOrder";
      }

      $result = $mysqli->query($selectQuery);

      // Display booking details in a table
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . $row['id'] . "</td>";
              echo "<td>" . $row['room_type'] . "</td>";
              echo "<td>" . $row['num_rooms'] . "</td>";
              echo "<td>" . $row['checkin_date'] . "</td>";
              echo "<td>" . $row['num_days'] . "</td>";
              echo "<td>" . $row['special_requests'] . "</td>";
              echo "<td>" . $row['total_price'] . "</td>";
              echo "<td><img src='" . $row['avatar'] . "' alt='Screenshot of payment receipt' style='width: 100px;'></td>";
              echo "<td>" . $row['email'] . "</td>";
              echo "<td>";
              echo "<a href='delete_booking.php?id=" . $row['id'] . "' class='delete'>Delete Booking</a>";
              echo "</td>";
              echo "<td>";
              echo "<a href='confirm_booking.php?id=" . $row['id'] . "' class='confirm'>Confirm Booking</a>";
              echo "</td>";
              echo "<td>";
              echo "<a href='cancel_booking.php?id=" . $row['id'] . "' class='cancel'>Cancel Booking</a>";
              echo "</td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='12'>No bookings found.</td></tr>";
      }

      // Close the database connection
      $mysqli->close();
      ?>
    </tbody>
  </table>
  <br>
  <div class="pagination">
    <script>
      // Pagination logic
      $(document).ready(function() {
        // Calculate number of pages based on number of bookings and items per page
        var numPages = Math.ceil(<?php echo $result->num_rows; ?> / 10);

        // Generate pagination links
        var paginationHtml = '';
        for (var i = 1; i <= numPages; i++) {
          var searchParam = '';
          if (window.location.search.includes('search')) {
            searchParam = '&' + window.location.search.split('?')[1];
          }
          paginationHtml += '<a href="?page=' + i + searchParam + '">' + i + '</a>';
        }

        // Display pagination links
        $('.pagination').html(paginationHtml);

        // Highlight active page
        var currentPage = parseInt(new URLSearchParams(window.location.search).get('page')) || 1;
        $('.pagination a').eq(currentPage - 1).addClass('active');

        // Sort functionality
        $('#bookings-table th').click(function() {
          var sortColumn = $(this).attr('data-sort');
          var sortOrder = 'asc';
          if ($(this).hasClass('asc')) {
            sortOrder = 'desc';
          }

          var urlParams = new URLSearchParams(window.location.search);
          urlParams.set('sort', sortColumn);
          urlParams.set('order', sortOrder);

          window.location.search = urlParams.toString();
        });
      });
	  // Function to insert new row
function insertNewRow(bookingData) {
  // Create table row element
  var newRow = $("<tr></tr>");
  
  // Add data to table cells
  newRow.append("<td>" ++ "' class='confirm'>Confirm Booking</a>");
  actionsCell.append("<a href='cancel_booking.php?id=" + bookingData.id + "' class='cancel'>Cancel Booking</a>");
  newRow.append(actionsCell);
  
  // Append the new row to the table body
  $("#bookings-table tbody").append(newRow);
}
	  
	  
    </script>
  </div>
</div>

</body>
</html>