function collapseInit() {
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
    .fadeToggle({
      duration: 300,
      easing: 'easeOutExpo',
    });
}

function switchInit() {
  $('select').each(function() {
    var $options = $('option', this);
    if( $options.length != 2 || !$options.is('[value=on], [value=off]') )
      return;

    var checked = $(this).val() == 'on';
    $(this)
      .wrap('<label class="switch" />')
      .hide()
      .before('<input type="checkbox" /><div><div></div></div>')
      .siblings('[type=checkbox]')
      .prop('checked', checked)
      .on('change', switchChange)
      .find('>div')
      .text($(this).val());
  });
}

function switchChange() {
  var value = $(this).is(':checked') ? 'on' : 'off';
  $(this)
    .siblings('select')
    .val(value);
}

function categoriesInit() {
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
