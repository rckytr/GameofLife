<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
} else {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: homepage.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome - Game of Life</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; background: #f0f0f0; padding-top: 60px; }
    table { margin: 0 auto; border-collapse: collapse; }
    td { width: 15px; height: 15px; border: 1px solid #ccc; background-color: #f0f0f0; }
    .alive { background-color: black; }
  </style>
</head>

<body>
<nav class="navbar navbar-light bg-light fixed-top">
  <div class="container-fluid">
    <span class="navbar-text">
      Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
    </span>
    <a href="?logout=1" class="btn btn-danger">Logout</a>
  </div>
</nav>

<div class="container text-center mt-4">
  <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
  <p>You are logged in. Enjoy Conway's Game of Life below.</p>

  <h2 class="mt-5">Conway's Game of Life</h2>

  <div id="grid-container" class="my-4"></div>

  <div class="btn-group mb-3">
    <button onclick="startGame()" class="btn btn-success">Start</button>
    <button onclick="stopGame()" class="btn btn-warning">Stop</button>
    <button onclick="nextGeneration()" class="btn btn-primary">Next Generation</button>
    <button onclick="run23Generations()" class="btn btn-info">+23 Generations</button>
    <button onclick="resetGame()" class="btn btn-danger">Reset</button>
  </div>

  <div>
    <label for="patternSelect" class="form-label">Load Pattern:</label>
    <select id="patternSelect" onchange="loadPattern()" class="form-select w-auto d-inline-block">
      <option value="">Select Pattern</option>
      <optgroup label="Still Lifes">
        <option value="block">Block</option>
      </optgroup>
      <optgroup label="Oscillators">
        <option value="blinker">Blinker</option>
      </optgroup>
      <optgroup label="Spaceships">
        <option value="glider">Glider</option>
      </optgroup>
      <optgroup label="Guns">
        <option value="gosper">Gosper Glider Gun</option>
      </optgroup>
    </select>
  </div>
</div>

<script>
const rows = 40, cols = 40;
let grid = [], interval = null, generations = 0;

const patterns = {
  block: [[10, 10], [10, 11], [11, 10], [11, 11]],
  blinker: [[10, 9], [10, 10], [10, 11]],
  glider: [[1, 2], [2, 3], [3, 1], [3, 2], [3, 3]],
  gosper: [
    [5,1],[5,2],[6,1],[6,2],
    [5,11],[6,11],[7,11],[4,12],[8,12],[3,13],[9,13],[3,14],[9,14],[6,15],[4,16],[8,16],[5,17],[6,17],[7,17],[6,18],
    [3,21],[4,21],[5,21],[3,22],[4,22],[5,22],[2,23],[6,23],[1,25],[2,25],[6,25],[7,25],
    [3,35],[4,35],[3,36],[4,36]
  ]
};

function createGrid() {
  const container = document.getElementById('grid-container');
  container.innerHTML = '';
  grid = [];
  const table = document.createElement('table');

  for (let i = 0; i < rows; i++) {
    const tr = document.createElement('tr');
    grid[i] = [];
    for (let j = 0; j < cols; j++) {
      const td = document.createElement('td');
      td.addEventListener('click', () => toggleCell(i, j));
      tr.appendChild(td);
      grid[i][j] = 0;
    }
    table.appendChild(tr);
  }
  container.appendChild(table);
}

function toggleCell(i, j) {
  grid[i][j] = grid[i][j] ? 0 : 1;
  updateGrid();
}

function updateGrid() {
  const cells = document.querySelectorAll('#grid-container td');
  let index = 0;
  for (let i = 0; i < rows; i++) {
    for (let j = 0; j < cols; j++) {
      cells[index++].className = grid[i][j] ? 'alive' : '';
    }
  }
}

function nextGeneration() {
  const newGrid = [];
  for (let i = 0; i < rows; i++) {
    newGrid[i] = [];
    for (let j = 0; j < cols; j++) {
      let aliveNeighbors = 0;
      for (let x = -1; x <= 1; x++) {
        for (let y = -1; y <= 1; y++) {
          if (x === 0 && y === 0) continue;
          const ni = i + x, nj = j + y;
          if (ni >= 0 && ni < rows && nj >= 0 && nj < cols) {
            aliveNeighbors += grid[ni][nj];
          }
        }
      }
      newGrid[i][j] = (grid[i][j] && (aliveNeighbors === 2 || aliveNeighbors === 3)) || (!grid[i][j] && aliveNeighbors === 3) ? 1 : 0;
    }
  }
  grid = newGrid;
  updateGrid();
  generations++;
}

function startGame() {
  if (!interval) interval = setInterval(nextGeneration, 300);
}

function stopGame() {
  clearInterval(interval);
  interval = null;
}

function run23Generations() {
  for (let i = 0; i < 23; i++) nextGeneration();
}

function resetGame() {
  clearInterval(interval);
  interval = null;
  generations = 0;
  createGrid();
}

function loadPattern() {
  const pattern = document.getElementById('patternSelect').value;
  resetGame();
  if (patterns[pattern]) {
    patterns[pattern].forEach(([i, j]) => {
      if (i >= 0 && i < rows && j >= 0 && j < cols) grid[i][j] = 1;
    });
    updateGrid();
  }
}

createGrid();
</script>

</body>
</html>
