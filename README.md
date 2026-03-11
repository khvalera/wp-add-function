Mu-Plugins for WP with additional common features.

====================================

class_href_button - Used to create a button using href.

  Class variables:
  text - the text on the button
  title - tooltip
  link_page - the address of the document to go to
  current_user_can - user rights

Usage example: $test = new class_href_button( 'New', '?page=cards', 'Test', 'edit');

# НАБІР РЕЖИМІВ (MODE)

У системі використовується **єдиний керуючий параметр**:

mode = list | history | deleted | edit | create | view


`mode` є **єдиним перемикачем UI та логіки**.  
Він визначає:
- тип відображення інтерфейсу;
- доступні дії користувача;
- рівень доступу (read / write);
- поведінку таблиць і форм.

---

## Таблиця відповідності режимів

| mode      | Що це                              | Хто бачить        | Поведінка / UI-логіка                                     |
|-----------|------------------------------------|-------------------|-----------------------------------------------------------|
| `list`    | Основний журнал / довідник         | всі               | Таблиця, editable (залежно від прав), фільтри, сортування |
| `history` | Історія документа / елемента       | admin, viewer     | Таблиця, **readonly**, без змін даних                     |
| `deleted` | Помічені на видалення              | admin             | Таблиця, **readonly + restore**, без редагування          |
| `edit`    | Редагування існуючого елемента     | editor            | Форма, editable, збереження змін                          |
| `create`  | Створення нового елемента          | editor            | Форма, editable, створення запису                         |
| `view`    | Перегляд елемента                  | viewer            | Форма, **readonly**, без дій                              |

---

## СТАН ТА НАВІГАЦІЯ (STATE)

Що вважається станом журналу:

STATE = {
  page,
  mode,
  paged,
  orderby,
  order,
  s,
  filters (f[], t[]),
  auto_filters (field-*),
  period (date1, date2)
}

Важливий принцип:

Стан НЕ показується користувачу явно
і НЕ змінюється вручну

Користувач:

натискає кнопки

переходить «Назад»

відкриває історію

Система:

сама зберігає / відновлює стан
