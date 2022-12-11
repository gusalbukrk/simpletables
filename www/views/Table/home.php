<?php if (!isset($_SESSION["user"])) : ?>
  <h4>Login or sign up to create tables</h4>
<?php else : ?>
  <h4 class="mb-3">Create database</h4>
  <form class="mb-5" method="post">
    <input class="border-dark border-opacity-75 rounded me-2" type="text" name="db" style="padding: 3px" pattern="\w+" required>
    <input class="btn btn-primary" type="submit" value="Create">
  </form>

  <h4 class="mb-3">Databases</h4>
  <ul>
    <?php foreach ($dbs as $db => $role) : ?>
      <li><a href="https://<?= $db ?>.simpletables.xyz"><?= $db ?> = <?= $role->name ?></a></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
