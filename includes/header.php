<header>
    <div class="header-container">
    <div class="header-logo">
        <img src="images/logo.png" alt="Clinic Logo">
            <p class="user-name"><?php echo $_SESSION['username']; ?></p>
    </div>
            <nav class="header-nav">
                <a href="user_home.php" <?php if($active === 'home'){ echo 'data-active'; } ?>>Home</a>
                <a href="clinic.php" <?php if($active === 'clinic'){ echo 'data-active'; } ?>>Clinics</a>
                <a href="personnel.php" <?php if($active === 'personnel'){ echo 'data-active'; } ?>>Personnel</a>
                <a href="patient.php" <?php if($active === 'patient'){ echo 'data-active'; } ?>>Patient</a>
                <a href="appointment.php" <?php if($active === 'appointment'){ echo 'data-active'; } ?>>Appointments</a>
                <a href="cases.php" <?php if($active === 'cases'){ echo 'data-active'; } ?>>Cases</a>
                <a href="user_messages.php" <?php if($active === 'messages'){ echo 'data-active'; } ?>>Messages</a>
            </nav>
            <form action="logout.php" method="POST">
                <button class="exit" type="submit">Logout</button>
            </form>
    </div>
</header>