<?php

	class discordlink_message {
		/** @var int */
		private $channelID;

		/** @var string */
		private $message;

		/** @var array<discordlink_embed> */
		private $embeds = array();

		/** @var array<discordlink_files> */
		private $files = array();

		/**
		 * @return int
		 */
		public function getChannelID(): int
		{
			return $this->channelID;
		}

		/**
		 * @param int $channelID
		 */
		public function setChannelID(int $channelID): void
		{
			$this->channelID = $channelID;
		}

		/**
		 * @return string
		 */
		public function getMessage(): string
		{
			return $this->message;
		}

		/**
		 * @param string $message
		 */
		public function setMessage(string $message): void
		{
			$this->message = $message;
		}

		/**
		 * @return discordlink_embed[]
		 */
		public function getEmbeds(): array
		{
			return $this->embeds;
		}

		/**
		 * @param discordlink_embed[] $embeds
		 */
		public function setEmbeds(array $embeds): void
		{
			$this->embeds = $embeds;
		}

		/**
		 * @param discordlink_embed $embed
		 */
		public function addEmbed(discordlink_embed $embed): void {
			$this->embeds[] = $embed;
		}

		/**
		 * @return \discordlink_files[]
		 */
		public function getFiles(): array
		{
			return $this->files;
		}

		/**
		 * @param \discordlink_files[] $files
		 */
		public function setFiles(array $files): void
		{
			$this->files = $files;
		}

		/**
		 * @param \discordlink_files $file
		 */
		public function addFiles(discordlink_files $file): void
		{
			$this->files[] = $file;
		}

		/**
		 * @param string $channelID
		 *
		 * @return string[]
		 */
		public function build(string $channelID): array
		{
			$buildMessage = array(
				"action"=>"sendDiscordMessage",
				"channelID"=>$channelID,
			);

			if ($this->message !== null) $buildMessage['message'] = $this->message;
			if (count($this->embeds) !== 0) {
				foreach ($this->embeds as $embed) {
					$buildEmbed = $embed->build();
					$buildMessage['embeds'][] = $buildEmbed;
				}
			}
			if (count($this->files) !== 0) {
				foreach ($this->files as $file) {
					$buildFile = $file->build();
					$buildMessage['files'][] = $buildFile;
				}
			}

			return $buildMessage;
		}
	}

	class discordlink_embed {
		/** @var string $title */
		private $title;

		/** @var string $color */
		private $color = "#ff0000";

		/** @var string $url */
		private $url;

		/** @var string $description */
		private $description;

		/** @var array<discordlink_field> $fields */
		private $fields = [];

		/** @var string $footer */
		private $footer;

		/** @var string $thumbnail */
		private $thumbnail;

		/**
		 * @return string
		 */
		public function getTitle(): string
		{
			return $this->title;
		}

		/**
		 * @param string $title
		 */
		public function setTitle(string $title): void
		{
			$this->title = $title;
		}

		/**
		 * @return string
		 */
		public function getColor(): string
		{
			return $this->color;
		}

		/**
		 * @param string $color
		 */
		public function setColor(string $color): void
		{
			$this->color = $color;
		}

		/**
		 * @return string
		 */
		public function getUrl(): string
		{
			return $this->url;
		}

		/**
		 * @param string $url
		 */
		public function setUrl(string $url): void
		{
			$this->url = $url;
		}

		/**
		 * @return string
		 */
		public function getDescription(): string
		{
			return $this->description;
		}

		/**
		 * @param string $description
		 */
		public function setDescription(string $description): void
		{
			$this->description = $description;
		}

		/**
		 * @return string
		 */
		public function getFooter(): string
		{
			return $this->footer;
		}

		/**
		 * @param string $footer
		 */
		public function setFooter(string $footer): void
		{
			$this->footer = $footer;
		}

		/**
		 * @return string
		 */
		public function getThumbnail(): string
		{
			return $this->thumbnail;
		}

		/**
		 * @param string $thumbnail
		 */
		public function setThumbnail(string $thumbnail): void
		{
			$this->thumbnail = $thumbnail;
		}

		/**
		 * @return discordlink_field[]
		 */
		public function getFields(): array
		{
			return $this->fields;
		}

		/**
		 * @param discordlink_field[] $fields
		 */
		public function setFields(array $fields): void
		{
			$this->fields = $fields;
		}

		/**
		 * @param discordlink_field $field
		 */
		public function addFields(discordlink_field $field): void
		{
			$this->fields[] = $field;
		}

		/**
		 * @return array
		 */
		public function build(): array
		{
			$buildEmbed = array();
			if ($this->description !== null) $buildEmbed['description'] = $this->description;
			if ($this->thumbnail !== null) $buildEmbed['thumbnail'] = $this->thumbnail;
			if ($this->footer !== null) $buildEmbed['footer'] = $this->footer;
			if ($this->title !== null) $buildEmbed['title'] = $this->title;
			if ($this->color !== null) $buildEmbed['color'] = $this->color;
			if ($this->url !== null) $buildEmbed['url'] = $this->url;
			if (count($this->fields) !== 0) {
				foreach ($this->fields as $field) {
					$buildField = $field->build();
					$buildEmbed['fields'][] = $buildField;
				}
			}

			return $buildEmbed;
		}
	}

	class discordlink_field {

		/** @var string $name */
		private $name;

		/** @var string $value */
		private $value;

		/** @var bool $inline */
		private $inline = TRUE;

		/**
		 * @return string
		 */
		public function getName(): string
		{
			return $this->name;
		}

		/**
		 * @param string $name
		 */
		public function setName(string $name): void
		{
			$this->name = $name;
		}

		/**
		 * @return string
		 */
		public function getValue(): string
		{
			return $this->value;
		}

		/**
		 * @param string $value
		 */
		public function setValue(string $value): void
		{
			$this->value = $value;
		}

		/**
		 * @return bool
		 */
		public function isInline(): bool
		{
			return $this->inline;
		}

		/**
		 * @param bool $inline
		 */
		public function setInline(bool $inline): void
		{
			$this->inline = $inline;
		}

		public function build(): array
		{
			$buildField = array();
			if ($this->name !== null) $buildField['name'] = $this->name;
			if ($this->value !== null) $buildField['value'] = $this->value;
			if ($this->inline !== null) $buildField['inline'] = $this->inline;

			return $buildField;
		}

	}

	class discordlink_files {

		/** @var string */
		private $name;

		/** @var string */
		private $attachment;

		/** @var string */
		private $description;

		/**
		 * @return string
		 */
		public function getName(): string
		{
			return $this->name;
		}

		/**
		 * @param string $name
		 */
		public function setName(string $name): void
		{
			$this->name = $name;
		}

		/**
		 * @return string
		 */
		public function getAttachment(): string
		{
			return $this->attachment;
		}

		/**
		 * @param string $attachment
		 */
		public function setAttachment(string $attachment): void
		{
			$this->attachment = $attachment;
		}

		/**
		 * @return string
		 */
		public function getDescription(): string
		{
			return $this->description;
		}

		/**
		 * @param string $description
		 */
		public function setDescription(string $description): void
		{
			$this->description = $description;
		}

		/**
		 * @return array
		 */
		public function build(): array
		{
			$buildFile = array();
			if ($this->name !== null) $buildFile['name'] = $this->name;
			if ($this->attachment !== null) $buildFile['attachment'] = $this->attachment;
			if ($this->description !== null) $buildFile['description'] = $this->description;

			return $buildFile;
		}
	}

	class discordlink_buttons {

	}