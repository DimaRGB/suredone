jQuery(function($) {
  console.log('Description CSS Ready');
  initSwitchWsywyg();
  initCollapse();
  initSwitchSelect();
  initCategories();
  initResetDefaultButton();
  initTooltip();
});

function initTooltip() {
  $(':input').qtip({
    position: {
      my: 'top center',
      at: 'bottom center',
    },
    style: {
      classes: 'qtip-green qtip-shadow'
    }
  });
}

function initResetDefaultButton() {
  $('form:not(#sdin-search)')
    .append("<label><input type='button' class='btn btn-reset-default' value='Reset Default' /></label>")
    .find('.btn-reset-default')
    .on('click', resetDefaultButtonClick);
}

function resetDefaultButtonClick() {
  $(this)
    .closest('form')
    .find('input')
    .each(function() {
      var defaultValue = $(this).data('defaultValue');
      if( defaultValue )
        $(this).val(defaultValue);
    });
}

function initSwitchWsywyg() {
  $('.tinymce')
    .before("<label class='switch'><input type='checkbox' checked /><div><div></div></div></label>")
    .siblings('.switch')
    .on('change', switchWsywygChange);
}

function switchWsywygChange() {
  var checked = $(this)
    .find('[type=checkbox]')
    .is(':checked');
  $(this)
    .siblings('.mceEditor')
    .stop()
    .slideToggle(checked);
}

function initSwitchSelect() {
  $('select').each(function() {
    var $options = $('option', this);
    if( $options.length != 2 || !$options.is('[value=on], [value=off]') )
      return;

    var checked = $(this).val() == 'on';
    $(this)
      .hide()
      .wrap('<label class="switch" />')
      .before('<input type="checkbox" /><div><div></div></div>')
      .siblings('[type=checkbox]')
      .prop('checked', checked)
      .on('change', switchSelectChange)
      .find('>div')
      .text($(this).val());
  });
}

function switchSelectChange() {
  var value = $(this).is(':checked') ? 'on' : 'off';
  $(this)
    .siblings('select')
    .val(value);
}

function initCollapse() {
  var $table = $('#sd-content form .settings-form');
  $('.settings-section-title', $table).each(function() {
    if( $(this).parent().next().find('.settings-section-title').length )
      return;
    $(this)
      .prepend('<span class="active">–</span>')
      .parent()
      .addClass('collapse')
      .on('click', collapseSectionToggle);
  });
  $('tr', $table).last().addClass('non-collapsible');
  $('.collapse').click();
}

function collapseSectionToggle() {
  var text = $(this).next().is(':hidden') ? '–' : '+';
  $(this)
    .find('th>span')
    .toggleClass('active')
    .text(text)
    .end()
    .nextUntil('.collapse')
    .not('.non-collapsible')
    .stop()
    .fadeToggle({
      duration: 300,
      easing: 'easeOutExpo',
    });
}

function initCategories() {
  $('#sd-taxonomy').addClass('categories');
  $('.categories')
    .find('>div')
    .addClass('category')
    .end()
    .find('select')
    .categoryToggle()
    .on('change', categoryChange);
}

function categoryChange() {
  $(this)
    .closest('.categories')
    .find('select')
    .categoryToggle();
}

$.fn.categoryToggle = function() {
  this.each(function() {
    var enabled = $(this).is(':enabled');
    $(this)
      .closest('.category')
      .toggle(enabled);
  });
  return this;
}
