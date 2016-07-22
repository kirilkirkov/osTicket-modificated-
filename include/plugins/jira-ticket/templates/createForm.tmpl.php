<h3>Create new JIRA ticket</h3>
<hr />
<?php if(!$ticket || !$ticket->getId()){?>
	<p id="msg_error">&nbsp; Ticket NOT FOUND!.</p>
<?php }else{ ?>
	<form method="post" class="user" action="/jira/<?=$ticket->getId()?>/create" id="createForm">
		<input type="hidden" name="ticket_id" value="<?=$ticket->getId()?>" />
		<div>
			<div style="float: left; width: 50%">
				<b>Project :</b><br />
				<select name="project" id="project" class="search-input" style="width:95%;">
					<option value="">Select project</option>
					<?php foreach($projects as $project){ ?>
						<option value="<?=$project->id?>"><?=$project->name?> (<?=$project->key?>) </option>
					<?php } ?>
				</select>
			</div>
			<div style="float: left; width: 50%">
				
			</div>
			<div style="clear: both;"></div>
		</div>
		<div>
			<div style="float: left; width: 50%">
				<b>Assignee :</b><br />
				<select name="assignee" id="assignee" class="search-input" style="width:95%;">
					<option value="-1">Automatic</option>
					<option value="">Unassigned</option>
					<?php foreach($users as $user){ ?>
						<option value="<?=$user->name?>"><?=$user->displayName?> (<?=$user->name?>)</option>
					<?php } ?>
				</select>
			</div>
			<div style="float: left; width: 50%">
				<b>Issue type :</b><br />
				<select name="issuetype" id="issuetype" class="search-input" style="width:95%;"></select>
			</div>
			<div style="clear: both;"></div>
		</div>
		<div>
			<div style="float: left; width: 50%">
				<b>Priority :</b><br />
				<select name="priority" id="priority" class="search-input" style="width:95%;">
					<?php foreach($priorities as $priority){ ?>
						<option value="<?=$priority->id?>" <?=$priority->name=='Major'?'selected="selected"':''?>><?=$priority->name?></option>
					<?php } ?>
				</select>
			</div>
			<div style="float: left; width: 50%">
				<b>Due date :</b><br />
				<input type="text" name="duedate" id="duedate" style="width:95%;" />
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<div style="margin-bottom: 5px;">
			<b>Summary :</b><br />
			<input type="text" name="subject" value="<?php echo Format::htmlchars($ticket->getSubject()); ?>" class="search-input" style="width:100%;"/>
		</div>
		<div>
			<b>Description :</b><br />
			<textarea name="description" style="width:100%; height:200px;">
*The problem :*
 * 
 
*Original complaint :*
{quote}
<?=$lastMessageBody."\n"?>
{quote}

*Ticket link :*
 * http://<?=$_SERVER['SERVER_NAME']?>/scp/tickets.php?id=<?=$ticket->getId()?>
			</textarea>
		</div>
	    <p class="full-width">
	        <span class="buttons" style="float:left">
	            <input type="button" name="cancel" class="close"  value="Cancel">
	        </span>
	        <span class="buttons" style="float:right">
	            <input type="submit" value="Create ticket" />
	        </span>
	     </p>
	</form>
	<script>
		var issue_types = <?=json_encode($issue_types)?>;
		$(function(){
			$('#project').on('change', function(){
				$('#issuetype').empty();
				var id = $(this).val();
				if(id>0){
					$.each(issue_types[id], function() {
					    $('#issuetype').append($("<option />").val(this.id).text(this.name));
					});
				}
			});
			$('#createForm').on('submit', function(e){
				e.preventDefault();
				
				if(!$('#project').val()){
					alert('Please select project!');
					return false;
				}
				
				if(!$('#issuetype').val()){
					alert('Please select issue type!');
					return false;
				}
				
				//$('#loading').show();
			});
			$('#duedate').datepicker({
				dateFormat: 'dd.mm.yy'
			});
		});
	</script>
<?php } ?>