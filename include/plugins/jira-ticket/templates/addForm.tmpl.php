<h3>Add existing JIRA ticket</h3>
<hr />
<?php if(!$ticket || !$ticket->getId()){?>
	<p id="msg_error">&nbsp; Ticket NOT FOUND!.</p>
<?php }else{ ?>
	<form method="post" class="user" action="/jira/<?=$ticket->getId()?>/add">
		<input type="hidden" name="ticket_id" value="<?=$ticket->getId()?>" />
		<div>
			<b>Ticket number or URL :</b><br />
			<input type="text" name="number" value="" class="search-input" style="width:100%;"/>
		</div>
	    <p class="full-width">
	        <span class="buttons" style="float:left">
	            <input type="button" name="cancel" class="close"  value="Cancel">
	        </span>
	        <span class="buttons" style="float:right">
	            <input type="submit" value="Add ticket" onclick="$(this).hide();" ondblclick="$(this).hide();" />
	        </span>
	     </p>
	</form>
<?php } ?>