<?php
include 'includes/session.php';
include 'includes/header.php';
?>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <div class="content-wrapper">
      <section class="content-header">
        <h1> All Permissions</h1>
      </section>

      <section class="content">
        <?php
        if (isset($_SESSION['error'])) {
          echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
          unset($_SESSION['success']);
        }
        ?>

        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Permission Management</h3>
              </div>

              <div class="box-body">
                <div class="row">

                  <?php
                  $userid = isset($_GET['id']) ? intval($_GET['id']) : 0;
                  ?>
                  <!-- LEFT USER LIST -->
                  <div class="col-md-3">
                    <div class="heading mb-10"><b>Select User</b></div>

                    <ul class="nav nav-pills nav-stacked">
                      <?php
                      $result = $conn->query("SELECT * FROM admin ORDER BY username ASC");
                      while ($row = $result->fetch_assoc()) {
                        $active = ($userid == $row['id']) ? 'active' : '';
                        echo "<li class='$active'>
                        <a href='permission.php?id={$row['id']}'>
                          <i class='fa fa-user'></i> {$row['username']}
                        </a>
                        </li>";
                      }
                      ?>
                    </ul>
                  </div>

                  <!-- RIGHT PERMISSIONS -->
                  <div class="col-md-9">
                    <?php
                    if ($userid) {
                      include 'permission_grid.php';
                    }
                    ?>
                  </div>


                </div>
              </div>

            </div>
          </div>
        </div>

      </section>
    </div>

    <?php include 'includes/scripts.php'; ?>
    <script src="assets/permission.js"></script>
</body>

</html>