<span class="action-button" data-dropdown="#action-dropdown-trac">
    <span ><i class="icon-calendar"></i> TRAC</span>
    <i class="icon-caret-down"></i>
</span>
<div id="action-dropdown-trac" class="action-dropdown anchor-right">
  <ul>
  	 <li><a class="trac-action" href="#trac/<?=$ticketID?>/create"><i class="icon-file-alt"></i> Create new Trac ticket</a></li>
  	 <li><a class="trac-action" href="#trac/<?=$ticketID?>/add"><i class="icon-file-text-alt"></i> Add existing Trac ticket</a></li>
  </ul>
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.trac-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        var dialog = $.dialog(url, [299,200]);
		
		$( document ).ajaxComplete(function(event, xhr, settings) {
			if(url == settings.url && xhr.status == 299){
				window.open(xhr.responseText);
				$(document).off('ajaxComplete');
			}
		});
    });
});
</script>