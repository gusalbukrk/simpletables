<div class="d-flex align-items-center mb-5">
  <a href="https://<?= $table === "users" ? "" : "$db." ?>simpletables.xyz" class="me-3">
    <i class="fa-solid fa-circle-arrow-left fs-4"></i>
  </a>
  <h2 class="fs-4 mb-0">
    <span class="p-1 me-1 bg-lighter-blue"><?= explode("'", $title)[0] ?></span>'<?= explode("'", $title)[1] ?>
  </h2>
</div>
<?php if (!$db_exists) : ?>
  <p class="fw-bold">Database doesn't exist.</p>
<?php elseif (!$table_exists) : ?>
  <p class="fw-bold">Table doesn't exist.</p>
<?php else : ?>
  <?php
  $get_field_schema = function ($name) use ($schema) {
    return current(
      array_filter($schema, function ($column) use ($name) {
        return $column["Field"] === $name;
      })
    );
  };

  $get_field_type = function ($name) use ($get_field_schema) {
    // get the Type property of the schema's field named $name
    // remove any extra info inside parentheses (e.g. 'char(50)' => 'char')
    $Type = preg_replace(
      "/\(.*$/",
      "",
      $get_field_schema($name)["Type"],
    );

    // return either a valid type attribute for input element (e.g. text) or
    // the exact type name taken from schema,
    // if it cannot be represent by one of the types of the input element (e.g. enum)
    return ["char" => "text", "varchar" => "text", "int" => "number"][$Type] ?? $Type;
  };

  $pk_name = current(array_filter($schema, function ($column) {
    return $column["Key"] === "PRI";
  }))["Field"];
  ?>
  <?php if (empty($records)) : ?>
    <p class="fw-bold mb-5">No records found.</p>
  <?php else : ?>
    <table id="records" class="table table-hover mb-5">
      <thead>
        <tr class="row g-0">
          <th class="col"></th>
          <?php foreach ($schema as $column) : ?>
            <th class="col text-capitalize"><?= $column["Field"] ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($records as $index => $record) : ?>
          <tr class="row g-0">
            <td class="col d-flex justify-content-center align-items-center">
              <!-- form can be placed inside td but not inside table or tr; because a form per row
            is needed, form attribute will be used to associate inputs w/ form -->
              <form id="<?= "record-{$index}" ?>" method="post">
                <input type="hidden" name="db" value="<?= $db ?>">
                <input type="hidden" name="table" value="<?= $table ?>">
                <input type="hidden" name="pkName" value="<?= $pk_name ?>">
                <?php foreach ($record as $name => $value) : ?>
                  <input type="hidden" name="record[<?= $name ?>]" value="<?= $value ?>">
                <?php endforeach; ?>
                <!-- if form has multiple buttons/submit inputs, the first button
              is the one triggered when the enter key is pressed -->
                <input type="submit" form="<?= "record-{$index}" ?>" name="action" value="update" hidden>
              </form>
              <button class="updateButton btn p-0 me-3 border-0" <?php if ($role === "reader" || ($role === "editor" && $table === "users")) echo "disabled"; ?>>
                <i class="fa-solid fa-pen-to-square text-orange fs-1dot125"></i>
              </button>
              <button form="<?= "record-{$index}" ?>" class="btn p-0 border-0" name="action" value="delete" <?php if ($role === "reader" || ($role === "editor" && $table === "users")) echo "disabled"; ?>>
                <i class="fa-solid fa-trash text-danger fs-1dot125"></i>
              </button>
            </td>
            <?php foreach ($record as $name => $value) : ?>
              <td class="col">
                <?php $type = $get_field_type($name); ?>
                <?php if ($type === "enum") : ?>
                  <select name="inputs[<?= $name ?>]" form="<?= "record-{$index}" ?>" class="form-select form-select-sm border-dark border-opacity-50 border-width-2" required disabled>
                    <?php preg_match_all("/(?<=')\w*(?=')/", $get_field_schema($name)["Type"], $matches); ?>
                    <?php foreach ($matches[0] as $match) : ?>
                      <option value="<?= $match ?>" <?= $match === $value ? "selected" : "" ?>><?= ucfirst($match) ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php else : ?>
                  <!-- $type must be a valid "type" attribute value for input element -->
                  <input name="inputs[<?= $name ?>]" type="<?= $type ?>" form="<?= "record-{$index}" ?>" value="<?= $value ?>" class="border-0 w-100 bg-transparent text-dark" required disabled>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <form class="max-w-350" method="post">
    <fieldset <?php if ($role === "reader" || ($role === "editor" && $table === "users")) echo "disabled"; ?>>
      <input type="hidden" name="db" value="<?= $db ?>">
      <input type="hidden" name="table" value="<?= $table ?>">
      <?php foreach ($schema as $column) : ?>
        <div class="mb-3">
          <label for="<?= $column["Field"] ?>Input" class="form-label fs-dot9 fw-bold text-dark-gray"><?= ucfirst($column["Field"]) ?></label>
          <?php $type = $get_field_type($column["Field"]); ?>
          <?php if ($type === "enum") : ?>
            <select id="<?= $column["Field"] ?>Input" name="record[<?= $column["Field"] ?>]" class="form-select border-dark border-opacity-50 border-width-2">
              <?php preg_match_all("/(?<=')\w*(?=')/", $column["Type"], $matches); ?>
              <?php foreach ($matches[0] as $match) : ?>
                <option value="<?= $match ?>"><?= ucfirst($match) ?></option>
              <?php endforeach; ?>
            </select>
          <?php else : ?>
            <input id="<?= $column["Field"] ?>Input" type="<?= $type ?>" name="record[<?= $column["Field"] ?>]" class="form-control border-dark border-opacity-50 border-width-2">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <input class="btn btn-success fw-bold" type="submit" name="action" value="create">
    </fieldset>
  </form>
  <script>
    // iterate through every row of the table used to list the records
    // in order to add the following event: when one of the rename buttons is clicked,
    // focus on the first input of that respective row
    document.querySelectorAll('table#records tbody tr').forEach(row => {
      // get immediate descendant (e.g. input, select) for every td element in current row
      // but ignore the first td because it only contains action buttons
      const inputs = row.querySelectorAll('td:not(:first-child) > *');

      // when this row's update button is clicked, enable all inputs inside row
      row.querySelector('td:nth-child(1) button.updateButton').addEventListener('click', e => {
        inputs.forEach(input => {
          input.disabled = false;
        });

        inputs[0].focus();
        inputs[0].select();
      });
    })

    // submit form when enter key is pressed inside a select element
    document.querySelectorAll('table#records tbody tr select').forEach(select => {
      select.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
          e.preventDefault();
          select.form.requestSubmit(
            e.target.form.querySelector('input[type="submit"][value="update"]')
          );
        }
      });
    });
  </script>
<?php endif; ?>
