<form action="<?php echo $action; ?>" method="POST">
  <div class="buttons">
    <div class="pull-right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="btn btn-primary" id="submit"/>
    </div>
  </div>
  <script type="text/javascript">
    $(document).ready(function () {
      $('#submit').click(function (e) {
        e.preventDefault();
        $.ajax({
          url: 'index.php?route=payment/kvapay/checkout',
          dataType: 'json',
          success: function (response) {
            if (response.state === 'ok') {
              window.location.href = response['url'];
            } else if (response.state === 'error') {
              alert(response.error);
            }
          }
        })
      })
    })
  </script>
</form>