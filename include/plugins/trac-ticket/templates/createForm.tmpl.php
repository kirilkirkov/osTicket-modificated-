<h3>Create new Trac ticket</h3>
<hr />
<?php if(!$ticket || !$ticket->getId()){?>
	<p id="msg_error">&nbsp; Ticket NOT FOUND!.</p>
<?php }else{ ?>
	<form method="post" class="user" action="/trac/<?=$ticket->getId()?>/create">
		<input type="hidden" name="ticket_id" value="<?=$ticket->getId()?>" />
		<div>
			<b>Ticket summary :</b><br />
			<input type="text" name="subject" value="<?php echo Format::htmlchars($ticket->getSubject()); ?>" class="search-input" style="width:100%;"/>
		</div>
		<div>
			<b>Ticket description :</b><br />
			<textarea name="description" style="width:100%; height:250px;">
'''The problem :'''
 * 
 
'''Original complaint : '''
{{{

<?=Format::html2text($ticket->getLastMessage())?>

}}}

'''Ticket link : '''
 * http://<?=$_SERVER['SERVER_NAME']?>/scp/tickets.php?id=<?=$ticket->getId()?>
			</textarea>
		</div>
	    <p class="full-width">
	        <span class="buttons" style="float:left">
	            <input type="button" name="cancel" class="close"  value="Cancel">
	        </span>
	        <span class="buttons" style="float:right">
	            <input type="submit" value="Create ticket" onclick="$(this).hide();" ondblclick="$(this).hide();" />
	        </span>
	     </p>
	</form>
<?php } ?>