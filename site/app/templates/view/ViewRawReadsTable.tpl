<div class='orcaRawReadsTableWrap'>
	<h3>{{ GROUP_NAME }}</h3>
	<h5 class='marginBotSm'>{{ FILE_NAME }}</h5>
	<hr />
	{% if RAW_READS %}
	<table class='orcaRawReadsTable table table-striped table-bordered table-responsive table-condensed'>
		<thead>
			<tr>
				<th>sgRNA</th>
				<th>Read Count</th>
			</tr>
		</thead>
		<tbody>
		{% for sgRNA, rawReads in RAW_READS %}
			<tr>
				<td>{{ sgRNA }}</td>
				<td>{{ rawReads }}</td>
			</tr>
		{% endfor %}
		</tbody>
	</table>
	{% else %}
		No Read Details Available...
	{% endif %}
</div>