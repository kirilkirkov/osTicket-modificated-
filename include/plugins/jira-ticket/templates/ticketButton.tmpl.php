<span class="action-button" data-dropdown="#action-dropdown-jira">
    <span ><i class="icon-calendar"></i> JIRA</span>
    <i class="icon-caret-down"></i>
</span>
<div id="action-dropdown-jira" class="action-dropdown anchor-right">
  <ul>
  	 <li><a class="jira-action" href="#jira/<?=$ticketID?>/create"><i class="icon-file-alt"></i> Create new JIRA ticket</a></li>
  	 <li><a class="jira-action" href="#jira/<?=$ticketID?>/add"><i class="icon-file-text-alt"></i> Add existing JIRA ticket</a></li>
  </ul>
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.jira-action', function(e) {
        e.preventDefault();
        //$('#overlay, #loading').show();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        var dialog = $.dialog(url, [200,299],
        	function(xhr){
        		if(xhr.status == 299){
	        		$('#loading').hide();
	        		window.open(xhr.responseText);
        		}
			},
			{
		        onshow: function(){
			        $('#loading').hide();
		        }
			}
		);
    });
    $(document).ajaxComplete(function(event, xhr, settings) {
		 //$('#loading').hide();
	});
});
</script>