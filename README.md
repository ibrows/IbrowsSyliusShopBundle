# Ibrows Sylius Shop Bundle

## Template

### Override general terms

```{.twig}
{% block _ibr_sylius_summary_termsAndConditions_label %}
{% spaceless %}
{% if not compound %}
    {% set label_attr = label_attr|merge({'for': id}) %}
{% endif %}
{% set label_attr = label_attr|merge({'class': (label_attr.class|default('') ~ ' checkbox')|trim}) %}
{% if attr.inline is defined and attr.inline %}
    {% set label_attr = label_attr|merge({'class': (label_attr.class|default('') ~ ' inline')|trim}) %}
{% endif %}
{% if required %}
    {% set label_attr = label_attr|merge({'class': (label_attr.class|default('') ~ ' required')|trim}) %}
{% endif %}
{% endspaceless %}
<label{% for attrname, attrvalue in label_attr %} {{ attrname }}="{{ attrvalue }}"{% endfor %}>
    {{ form_widget(form) }} {{ "form.ibr_sylius_summary.termsandconditions"|trans({'%agburl%': 'url'})}, translation_domain)|raw }}
</label>
{% endblock %}
```
