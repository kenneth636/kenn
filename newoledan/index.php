<?php
// ✅ Connect to database
$conn = new mysqli("localhost", "root", "", "systems");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// ✅ Delete event if ?delete=ID is set
if (isset($_GET['delete'])) {
  $idToDelete = intval($_GET['delete']);
  $conn->query("DELETE FROM tbl_calendar WHERE id = $idToDelete") or die("Delete failed: " . $conn->error);
  header("Location: index.php");
  exit();
}

// ✅ Add event if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = $_POST['title'] ?? '';
  $date = $_POST['date'] ?? '';
  $hour = $_POST['hour'] ?? '';
  $minute = $_POST['minute'] ?? '';
  $ampm = $_POST['ampm'] ?? '';
  $desc = $_POST['description'] ?? '';

  if ($title && $date && $hour !== '' && $minute !== '' && $ampm && $desc) {
    // Convert time to 24-hour format
    $hour = intval($hour);
    $minute = intval($minute);
    if ($ampm === 'PM' && $hour !== 12) $hour += 12;
    if ($ampm === 'AM' && $hour === 12) $hour = 0;
    $time = sprintf('%02d:%02d:00', $hour, $minute);

    $stmt = $conn->prepare("INSERT INTO tbl_calendar (title, date, time, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $date, $time, $desc);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
  }
}

// ✅ Fetch all events
$result = $conn->query("SELECT * FROM tbl_calendar ORDER BY date, time");
$events = [];
while ($row = $result->fetch_assoc()) {
  $events[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Calendar System</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background-color: yellowgreen;
      margin-bottom: 200px;
    }
    form input, form textarea, form button, form select {
      display: block;
      margin: 10px 0;
      padding: 8px;
      width: 95%;
      max-width: 400px;
    }
    .event {
      border: none;
      margin: 10px 0;
      padding: 10px;
      position: relative;
    }
    .calendar-day {
     margin-right: 57%;
    }
    .foot{
        margin-right: 55%;
      border: none;
    }
    .delete-btn {
      position: relative;
      top: 10px;
      right: 10px;
      background-color: red;
      color: white;
      padding: 5px 10px;
      border: none;
      cursor: pointer;
    }
    .juice{
       width: 800px;
       padding: 10px;
       margin: 10px 0;
    }

    .dan{
  width: 400px; 
  height: 800px;
  border-radius: 10px;
  border-color: gray;
  border-style: solid;
  padding: 20px;
}
.b{
    width: 400px;
}
  </style>
</head>
<body>
    <center>
  <h1>Event Calendar System </h1>
 
  <div class="dan">
  <!-- Form to add event -->
  <form method="POST" action="">
    <input type="text" name="title" placeholder="Event Title" required>
    <input type="date" name="date" required>

    <!-- Time selection: Hour, Minute, AM/PM -->
    <label>Time:</label>
    <select name="hour" class="juice" required>
      <?php for ($h = 1; $h <= 12; $h++): ?>
        <option value="<?= $h ?>"><?= $h ?></option>
      <?php endfor; ?>
    </select>
    <select name="minute" class="juice" required>
      <?php for ($m = 0; $m < 60; $m += 5): ?>
        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
      <?php endfor; ?>
    </select>
    <select name="ampm" class="juice" required>
      <option value="AM">AM</option>
      <option value="PM">PM</option>
    </select>

    <textarea name="description" placeholder="Description" required></textarea>
    <button type="submit" class="b">Add Event</button>
  </form>

  <!-- Display upcoming events -->
   
  <h2 style="margin-right: 40%;">Upcoming Events</h2>
  <?php foreach ($events as $event): ?>
    <div class="event">
      <form method="GET" style="position: absolute; top: 10px; right: 10px;">
        <input type="hidden" name="delete" value="<?= $event['id'] ?>">
        <button type="submit" class="delete-btn" onclick="return confirm('Delete this event?')">Delete</button>
      </form>
      
      <h3 style="margin-right: 45%;"><?= htmlspecialchars($event['title']) ?></h3>
      <p style="margin-right: 50%;"><strong>Date:</strong> <?= $event['date'] ?></p>
      <p style="margin-right: 56%;"><strong>Time:</strong> <?= date("g:i A", strtotime($event['time'])) ?></p>
      <p style="margin-right: 77%;"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
  
    </div>
  <?php endforeach; ?>

  <!-- Display calendar view -->
   
  <h2 style="margin-right: 50%;">Calendar View</h2>
  <div class="foot">
  <?php
  $grouped = [];
  foreach ($events as $event) {
    $grouped[$event['date']][] = $event;
  }

  foreach ($grouped as $date => $eventsOnDate): ?>
  </div>

    <div class="calendar-day">
      <strong><?= $date ?></strong>
      <ul>
        <?php foreach ($eventsOnDate as $e): ?>
          <?= date("g:i A", strtotime($e['time'])) ?> - <?= htmlspecialchars($e['title']) ?>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
        
        </div>
        </center>
</body>
</html>
