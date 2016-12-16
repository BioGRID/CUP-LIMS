<div class='alert alert-{{ TYPE }} marginBotSm' role='alert'>
	<i class='processIcon fa {{ ICON }} fa-lg fa-fw'></i> 
	<span class='processText'>{{ PROCESS_PREAMBLE }} : <strong>{{ FILE_NAME }}</strong> ({{ FILE_SIZE }})</span>
	{% if ERRORS %}
		<ul>
			{% for ERROR in ERRORS|slice(0, 10) %}
				<li>{{ ERROR }}</li>
			{% endfor %}
		</ul>
	{% endif %}
</div>