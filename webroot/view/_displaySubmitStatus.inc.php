<?php
if(!empty($submitFeedback)) {
	$cssClass="is-success";
	if($submitFeedback['status'] == "error") {
		$cssClass="is-danger";
	}
	$message = $submitFeedback['message'];
	if(is_array($message)) {
		$message = implode("<br />", $message);
	}

	if(!empty($message)) {
?>
	<div class="columns">
		<div class="column">
			<div class="notification <?php echo $cssClass; ?>">
				<p><?php echo $message; ?></p>
			</div>
		</div>
	</div>
<?php
	}
}
?>
