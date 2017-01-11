<div class='btn-group'>
	<button type='button' class='btn {{ BTN_CLASS }} btn-sm dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-has-expanded='false'><i class='fa {{ BTN_ICON }}'></i> {{ BTN_TEXT }} <span class='caret'></span></button>
	<ul class='dropdown-menu'>
		{% for LINK_ID, LINK_INFO in LINKS %}
			{% if LINK_INFO.linkHREF %}
				<li><a id='{{ LINK_ID }}' class='{{ LINK_INFO.linkClass }}' href='{{ LINK_INFO.linkHREF }}'>{{ LINK_INFO.linkText }}</a></li>
			{% else %}
				<li><a id='{{ LINK_ID }}' class='{{ LINK_INFO.linkClass }}'>{{ LINK_INFO.linkText }}</a></li>
			{% endif %}
		{% endfor %}
	</ul>
</div>