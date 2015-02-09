<?php

class Action_AdminLogin extends Action_Admin
{

    protected function post($post)
    {
        if ($post['action'] === 'logout') {
            $_SESSION['admin'] = null;
        } elseif ($post['action'] === 'login') {
            $_SESSION['admin'] = serialize(Admin::login($post['email'], $post['password']));
            if (! empty($_SESSION['admin'])) {
                $this->redirectBack(Action_Admin::lt());
            } else {
                $this->error = "wrong email or password";
            }
        }
    }

    protected function sectionContent()
    {
        ?>

<div class="row" style="padding-top: 40px;">
	<div class="col-md-3">&nbsp;</div>
	<div class="col-md-6 well">

		<form class="form-horizontal" action="" method="post">
			<fieldset>

				<!-- Form Name -->
				<legend>Login</legend>
	  <?php if(!empty($this->error)): ?>
	      <div class="form-group bg-danger text-danger">
					<div class="col-md-4"></div>
					<div class="col-md-8">
	          <?php echo $this->error; ?>
	          </div>
				</div>
	  <?php endif; ?>
	  <!-- Text input-->
				<div class="form-group">
					<label class="col-md-4 control-label" for="email">Email</label>
					<div class="col-md-8">
						<input id="email" name="email" type="text" placeholder="Email"
							class="form-control input-md" required="">

					</div>
				</div>

				<!-- Password input-->
				<div class="form-group">
					<label class="col-md-4 control-label" for="password">Password</label>
					<div class="col-md-8">
						<input id="password" name="password" type="password"
							placeholder="Password" class="form-control input-md" required="">

					</div>
				</div>

				<!-- Button -->
				<div class="form-group">
					<label class="col-md-4 control-label" for="login"></label>
					<div class="col-md-4">
						<input type="hidden" name="action" value="login" />
						<button id="login" name="login" class="btn btn-primary">
							<span class="glyphicon glyphicon-chevron-right"></span> Login
						</button>
					</div>
				</div>

			</fieldset>
		</form>

	</div>
	<div class="col-md-3">&nbsp;</div>
</div>

<?php
    }
}
