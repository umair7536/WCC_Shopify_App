#Custom Forms

##Form can have sections 
* form_type to categories forms ( e.g Medical, Educational etc)
* content column to store detail and meta data of this form( e.g sections information ). Data will be json format 

### custom_forms.content columns data structure 

```$xslt
{
	"sections": [{
		"id": "1",
		"name": "Basic Information",
		"description": "basic information description"
	}, {
		"id": "2",
		"name": "Educations",
		"description": "Your education information"
	}],
	"template": {
		"id": "place id of template. future usage."
	},
	"properties": {
		"classes": "comma seperated list"
	}
}
```

### 
 
 
#Custom Form Fields 
 
##Form Fields Types 

* Text (input fields )
* Paragraph ( text area)
* Single Choice (Radio boxes)
* Multiple choice (multiple checkbox)
* options_list (selector)
* headings 

from config/constants.php file

```$xslt
"user_form_field_type" => array(
        "1" => "Text",
        "2" => "Paragraph",
        "3" => "Single Choice",
        "4" => "Multiple Choice",
        "5" => "Options List",
        "6" => "Heading 1",
        "7" => "Heading 2",
        "8" => "Heading 3",
        "9" => "Heading 4",
        "10" => "Heading 5",
        "11" => "Heading 5"
    )
```





### custom_forms_fields.content 


#### Text Field ( input field)


* title required
* default value of placeholder will be "Your answer"

```$xslt
{
	"title": "what is your name",
	"description": "complete your name",
	"attributes": {
		"placeholder": "Enter your name",
		"required": "required",
		"pattern": "A-Za-z"
	}
}
```

#### Paragraph ( Text Area)

* title required
* default value of placeholder will be "Your answer"

```$xslt
{
	"title": "tell us about yourself",
	"description": "write your SOP(statement of purpose)",
	"attributes": {
		"placeholder": "Enter your name",
		"rows": "3",
		"cols": "50"
		"required": "required",
		"pattern": "A-Za-z"
	}
}
```

#### Single Choice( radio buttton)

```$xslt
{
	"title": "what is your gender?",
	"description": "this is optional",
	"options": [{
			"input": {
				"checked": "checked",
				"classes": "classes with spaces"
			},
			"label": {
				"text": "Male"
			},
		},
		{
			"input": {
				"checked": "checked",
				"classes": "classes with spaces"
			},
			"label": {
				"text": "Female"
			},
		}
	],
	"attributes": {
		"required": "required"
	}
}
```


#### multiple choice box

```$xslt
{
	"title": "what are your skills?",
	"description": "this is optional",
	"options": [{
			"input": {
				"checked": "checked",
				"classes": "classes with spaces"
			},
			"label": {
				"text": "Adobe"
			},
		},
		{
			"input": {
				"checked": "checked",
				"classes": "classes with spaces"
			},
			"label": {
				"text": "Javascript"
			},
		}
	],
	"attributes": {
		"required": "required"
	}
}
```

#### option list 

```$xslt
{
	"title": "what is your gender *",
	"description": "this is optional",
	"options": [{
			"label": "Male",
			"value": "male",
			"selected": "selected"
		},
		{
			"label": "Female",
			"value": "female"
		}
	],
	"attributes": {
		"required": "required"
	}
}
```

#### heading 1
```$xslt
{
	"title": "This is heading 1",
}
```


