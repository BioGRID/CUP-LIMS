<div class='orcaFileAnnotation'>
	{% for FILE_ID, FILE_DETAILS in FILES %}
		<div>
			<div><strong>{{ FILE_DETAILS.LABEL }}</strong></div>
			<a href='{{ FILE_DETAILS.URL|raw }}' title='VIEW FILE {{ FILE_DETAILS.NAME }}'>{{ FILE_DETAILS.NAME }}</a>
		</div>
	{% endfor %}
</div>