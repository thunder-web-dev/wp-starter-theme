<?php

namespace CustomTheme\Faker\Helper;

use CustomTheme\Faker\Main;

class FIO_Collection extends Main {

	public function get_random_human_fio(): array {
		return random_int( 0, 1 ) ? $this->get_random_man_fio() : $this->get_random_woman_fio();
	}

	public function get_random_man_fio(): array {
		return [
			'name'        => $this->get_random_item( $this->get_man_names() ),
			'patronymics' => $this->get_random_item( $this->get_man_patronymics() ),
			'surnames'    => $this->get_random_item( $this->get_man_surnames() ),
		];
	}

	public function get_random_woman_fio(): array {
		return [
			'name'        => $this->get_random_item( $this->get_woman_names() ),
			'patronymics' => $this->get_random_item( $this->get_woman_patronymics() ),
			'surnames'    => $this->get_random_item( $this->get_woman_surnames() ),
		];
	}

	public function get_man_names(): array {
		return [
			"Александр",
			"Максим",
			"Сергей",
			"Андрей",
			"Алексей",
			"Дмитрий",
			"Иван",
			"Евгений",
			"Кирилл",
			"Николай",
			"Михаил",
			"Владимир",
			"Юрий",
			"Олег",
			"Игорь",
			"Роман",
			"Павел",
			"Артем",
			"Денис",
			"Антон",
			"Константин",
			"Владислав",
			"Борис",
			"Вячеслав",
			"Григорий",
			"Степан",
			"Леонид",
			"Виталий",
			"Федор",
			"Виктор",
			"Егор",
			"Георгий",
			"Петр",
			"Семен",
			"Тимофей",
			"Анатолий",
			"Валерий",
			"Геннадий",
			"Юрий",
			"Никита",
			"Артемий",
			"Даниил",
			"Станислав",
			"Вадим",
			"Матвей",
			"Марк",
			"Филипп",
			"Арсений",
			"Илья",
			"Елисей",
			"Руслан",
			"Глеб",
			"Святослав",
			"Альберт",
			"Ярослав",
			"Тимур",
			"Эдуард",
			"Олег",
			"Богдан",
			"Валентин",
			"Яков",
			"Валерий",
			"Александр",
			"Макар",
			"Савелий",
			"Анатолий",
			"Мирослав",
			"Герман",
			"Родион",
			"Игнат",
			"Марат",
			"Эмиль",
			"Платон",
			"Аскар",
			"Лев",
			"Артур",
			"Иосиф",
			"Ростислав",
			"Клим",
			"Рустам",
			"Амир",
			"Василий",
			"Дамир",
			"Самир",
			"Гордей",
			"Савва",
			"Ефим",
			"Давид",
			"Тихон",
			"Эльдар",
			"Оскар",
			"Кристиан",
			"Мирон",
			"Федот",
			"Ян",
			"Афанасий",
			"Адриан",
			"Лука",
			"Тарас",
			"Прохор",
		];
	}

	public function get_man_patronymics(): array {
		return [
			"Александрович",
			"Максимович",
			"Сергеевич",
			"Андреевич",
			"Алексеевич",
			"Дмитриевич",
			"Иванович",
			"Евгеньевич",
			"Кириллович",
			"Николаевич",
			"Михайлович",
			"Владимирович",
			"Юрьевич",
			"Олегович",
			"Игоревич",
			"Романович",
			"Павлович",
			"Артемович",
			"Денисович",
			"Антонович",
			"Константинович",
			"Владиславович",
			"Борисович",
			"Вячеславович",
			"Григорьевич",
			"Степанович",
			"Леонидович",
			"Виталиевич",
			"Федорович",
			"Викторович",
			"Егорович",
			"Георгиевич",
			"Петрович",
			"Семенович",
			"Тимофеевич",
			"Анатольевич",
			"Валерьевич",
			"Геннадьевич",
			"Юрьевич",
			"Никитич",
			"Артемьевич",
			"Даниилович",
			"Станиславович",
			"Вадимович",
			"Матвеевич",
			"Маркович",
			"Филиппович",
			"Арсеньевич",
			"Ильич",
			"Елисеевич",
			"Русланович",
			"Глебович",
			"Святославович",
			"Альбертович",
			"Ярославович",
			"Тимурович",
			"Эдуардович",
			"Олегович",
			"Богданович",
			"Валентинович",
			"Яковлевич",
			"Валерьевич",
			"Александрович",
			"Макарович",
			"Савельевич",
			"Анатольевич",
			"Мирославович",
			"Германович",
			"Родионович",
			"Игнатович",
			"Маратович",
			"Эмилевич",
			"Платонович",
			"Аскарович",
			"Львович",
			"Артурович",
			"Иосифович",
			"Ростиславович",
			"Климович",
			"Рустамович",
			"Амирович",
			"Васильевич",
			"Дамирович",
			"Самирович",
			"Гордеевич",
			"Саввич",
			"Ефимович",
			"Давидович",
			"Тихонович",
			"Эльдарович",
			"Оскарович",
			"Кристианович",
			"Миронович",
			"Федотович",
			"Янович",
			"Афанасьевич",
			"Адрианович",
			"Лукич",
			"Тарасович",
			"Прохорович",
		];
	}

	public function get_man_surnames(): array {
		return [
			"Иванов",
			"Смирнов",
			"Кузнецов",
			"Попов",
			"Васильев",
			"Петров",
			"Соколов",
			"Михайлов",
			"Новиков",
			"Федоров",
			"Морозов",
			"Волков",
			"Алексеев",
			"Лебедев",
			"Семенов",
			"Егоров",
			"Павлов",
			"Козлов",
			"Степанов",
			"Николаев",
			"Орлов",
			"Андреев",
			"Макаров",
			"Никитин",
			"Захаров",
			"Зайцев",
			"Соловьев",
			"Борисов",
			"Яковлев",
			"Григорьев",
			"Романов",
			"Воробьев",
			"Сергеев",
			"Кузьмин",
			"Фролов",
			"Александров",
			"Дмитриев",
			"Королев",
			"Гусев",
			"Киселев",
			"Ильин",
			"Максимов",
			"Поляков",
			"Сорокин",
			"Виноградов",
			"Ковалев",
			"Белов",
			"Медведев",
			"Антонов",
			"Тарасов",
			"Жуков",
			"Баранов",
			"Филиппов",
			"Комаров",
			"Давыдов",
			"Беляев",
			"Герасимов",
			"Богданов",
			"Осипов",
			"Сидоров",
			"Матвеев",
			"Титов",
			"Марков",
			"Миронов",
			"Крылов",
			"Куликов",
			"Карпов",
			"Власов",
			"Мельников",
			"Денисов",
			"Гаврилов",
			"Тихомиров",
			"Казаков",
			"Афанасьев",
			"Данилов",
			"Савельев",
			"Тимофеев",
			"Фомин",
			"Чернов",
			"Абрамов",
			"Мартынов",
			"Ефимов",
			"Федотов",
			"Щербаков",
			"Назаров",
			"Кудрявцев",
			"Лукин",
			"Быков",
			"Дроздов",
			"Логинов",
			"Сафонов",
			"Игнатьев",
			"Лапин",
			"Лебедев",
			"Коновалов",
			"Ермаков",
			"Петухов",
			"Гончаров",
			"Ромашов",
			"Горшков",
		];
	}

	public function get_woman_names(): array {
		return [
			"Анна",
			"Мария",
			"Екатерина",
			"Ольга",
			"Елена",
			"Наталья",
			"Татьяна",
			"Ирина",
			"Светлана",
			"Марина",
			"Анастасия",
			"Валентина",
			"Галина",
			"Людмила",
			"Вера",
			"Надежда",
			"Любовь",
			"Ксения",
			"Виктория",
			"Полина",
			"Юлия",
			"Дарья",
			"Александра",
			"Оксана",
			"Вероника",
			"Алёна",
			"Евгения",
			"София",
			"Маргарита",
			"Валерия",
			"Зоя",
			"Нина",
			"Лидия",
			"Раиса",
			"Алла",
			"Диана",
			"Кира",
			"Алиса",
			"Элина",
			"Лилия",
			"Инна",
			"Наталия",
			"Тамара",
			"Елизавета",
			"Мирослава",
			"Виолетта",
			"Жанна",
			"Элеонора",
			"Карина",
			"Олеся",
			"Лариса",
			"Агата",
			"Яна",
			"Ева",
			"Мила",
			"Эльвира",
			"Кристина",
			"Василиса",
			"Арина",
			"Валентина",
			"Альбина",
			"Юлиана",
			"Эвелина",
			"Марьяна",
			"Ангелина",
			"Леся",
			"Алиса",
			"Аделина",
			"Лина",
			"Зинаида",
			"Ольга",
			"Владислава",
			"Снежана",
			"Эльза",
			"Марта",
			"Майя",
			"Арина",
			"Глафира",
			"Есения",
			"Регина",
			"Роза",
			"Евдокия",
			"Авдотья",
			"Ника",
			"Инга",
			"Ефросиния",
			"Калерия",
			"Камилла",
			"Лада",
			"Милана",
			"Софья",
			"Стефания",
			"Татьяна",
			"Эмилия",
			"Ульяна",
			"Фаина",
			"Фаиза",
			"Юлия",
			"Ярослава",
			"Аида",
		];
	}

	public function get_woman_patronymics(): array {
		return [
			"Александровна",
			"Ивановна",
			"Сергеевна",
			"Владимировна",
			"Дмитриевна",
			"Николаевна",
			"Андреевна",
			"Петровна",
			"Михайловна",
			"Фёдоровна",
			"Викторовна",
			"Евгеньевна",
			"Алексеевна",
			"Юрьевна",
			"Олеговна",
			"Борисовна",
			"Павловна",
			"Константиновна",
			"Геннадьевна",
			"Леонидовна",
			"Григорьевна",
			"Романовна",
			"Максимовна",
			"Вячеславовна",
			"Станиславовна",
			"Аркадьевна",
			"Игоревна",
			"Анатольевна",
			"Васильевна",
			"Филипповна",
			"Эдуардовна",
			"Рустамовна",
			"Денисовна",
			"Артуровна",
			"Тимуровна",
			"Марковна",
			"Платоновна",
			"Семёновна",
			"Георгиевна",
			"Львовна",
			"Кирилловна",
			"Савельевна",
			"Фаддеевна",
			"Никитична",
			"Гавриловна",
			"Лукьяновна",
			"Игнатьевна",
			"Елизаровна",
			"Радионовна",
			"Мартыновна",
			"Глебовна",
			"Тарасовна",
			"Климовна",
			"Прохоровна",
			"Фоминична",
			"Артёмовна",
			"Яковлевна",
			"Захаровна",
			"Германовна",
			"Егоровна",
			"Владиславовна",
			"Мироновна",
			"Матвеевна",
			"Святославовна",
			"Андреевна",
			"Ивановна",
			"Алексеевна",
			"Романовна",
			"Юрьевна",
			"Фёдоровна",
			"Михайловна",
			"Даниловна",
			"Гавриловна",
			"Константиновна",
			"Евгеньевна",
			"Богдановна",
			"Антоновна",
			"Родионовна",
			"Андреевна",
			"Петровна",
			"Филипповна",
			"Геннадьевна",
			"Борисовна",
			"Леонидовна",
			"Владимировна",
			"Дмитриевна",
			"Николаевна",
			"Сергеевна",
			"Павловна",
			"Георгиевна",
			"Кирилловна",
			"Григорьевна",
			"Юлиановна",
			"Тимофеевна",
			"Ефимовна",
			"Ильинична",
			"Назаровна",
			"Ярославовна",
			"Тихоновна",
			"Емельяновна",
		];
	}

	public function get_woman_surnames(): array {
		return [
			"Иванова",
			"Петрова",
			"Сидорова",
			"Смирнова",
			"Кузнецова",
			"Попова",
			"Васильева",
			"Новикова",
			"Морозова",
			"Волкова",
			"Соколова",
			"Орлова",
			"Зайцева",
			"Соловьёва",
			"Карпова",
			"Виноградова",
			"Романова",
			"Ильина",
			"Савельева",
			"Медведева",
			"Фёдорова",
			"Михайлова",
			"Богданова",
			"Егорова",
			"Алексеенко",
			"Лебедева",
			"Козлова",
			"Григорьева",
			"Степанова",
			"Мельникова",
			"Мартынова",
			"Крылова",
			"Макарова",
			"Маслова",
			"Николаева",
			"Тимофеева",
			"Киселёва",
			"Кузьмина",
			"Миронова",
			"Симонова",
			"Мартынова",
			"Ларионова",
			"Карасева",
			"Кудрявцева",
			"Баранова",
			"Кочергина",
			"Сысоева",
			"Куликова",
			"Мальцева",
			"Назарова",
			"Рябова",
			"Тарасова",
			"Шестакова",
			"Пахомова",
			"Логинова",
			"Воронцова",
			"Семенова",
			"Чернова",
			"Жукова",
			"Шубина",
			"Полякова",
			"Беспалова",
			"Афанасьева",
			"Родионова",
			"Осипова",
			"Беляева",
			"Калинина",
			"Медведева",
			"Ефимова",
			"Демидова",
			"Соболева",
			"Павлова",
			"Потапова",
			"Громова",
			"Кошелева",
			"Ершова",
			"Матвеева",
			"Титова",
			"Ермакова",
			"Чернышева",
			"Захарова",
			"Сазонова",
			"Савина",
			"Голубева",
			"Филатова",
			"Миронова",
			"Фролова",
			"Филатова",
			"Быкова",
			"Кулакова",
			"Третьякова",
			"Лаврентьева",
			"Кузнецова",
			"Лукина",
			"Маркова",
			"Гаврилова",
			"Тихонова",
			"Андреева",
			"Павленко",
			"Варфоломеева",
		];
	}

}
