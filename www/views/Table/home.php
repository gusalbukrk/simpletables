<?php if (!isset($_SESSION["user"])) : ?>
  <h4>Login or sign up to create tables</h4>
<?php else : ?>
  <h4 class="mb-3">Create database</h4>
  <form class="mb-5" method="post">
    <input class="border-dark border-opacity-75 rounded me-2" type="text" name="db" style="padding: 3px" pattern="\w+" required>
    <input class="btn btn-primary" type="submit" name="action" value="Create">
  </form>
  <h4 class="mb-3">Databases</h4>
  <?php if ($dbs) : ?>
    <table class="table table-hover max-w-350">
      <thead>
        <tr>
          <th>Name</th>
          <th>Role</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dbs as $db => $role) : ?>
          <tr>
            <td><a href="https://<?= $db ?>.simpletables.xyz"><?= $db ?></a></td>
            <td><?= strtolower($role->name) ?></td>
            <td>
              <form method="post">
                <button class="btn m-0 p-0 border-0" name="action" value="Remove">
                  <input type="hidden" name="db" value="<?= $db ?>">
                  <i class="fa-solid fa-trash text-danger"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else : ?>
    <p class="fw-bold">No databases found.</p>
  <? endif; ?>
<?php endif; ?>
