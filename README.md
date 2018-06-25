# Value object generator


## Definition file (YAML file)



## Models

A model is a Value Object that can be used as part to formalise your data structures.   

### Model types

Each model you define must have an _type_, the code of which is responsible for rendering the generated value object. 

These types are defined externally to this project so you will need to refer to the documentation of your specific implementation for more details. 

### Defining models



  - name: Person
    type: String
    namespace: Funeralzone\FAS\PersonValueObjects\Person
    instantiationName: NullablePerson
    referenceName: Person






model:

  # External models
  # ==========================

  - name: EntityId
    namespace: Funeralzone\FAS\DomainEntities
    instantiationName: NumericEntityId
    referenceName: EntityId

  - name: DirectoryListingId
    namespace: Funeralzone\FAS\Common\ValueObjects\DirectoryListingId
    instantiationName: NullableDirectoryListingId
    referenceName: DirectoryListingId

  - name: Notes
    namespace: Funeralzone\FAS\NoteValueObjects\Notes
    instantiationName: NullableNotes
    referenceName: Notes

  - name: AuthorId
    namespace: Funeralzone\FAS\NoteValueObjects
    instantiationName: AuthorId
    referenceName: AuthorId

  - name: Content
    namespace: Funeralzone\FAS\NoteValueObjects
    instantiationName: Content
    referenceName: Content

  # Query models
  # ==========================

  - name: SearchLimit
    type: Integer
    relativeNamespace: Queries\SearchLimit

  - name: SearchTerm
    type: String
    relativeNamespace: Queries\SearchTerm

  # Data models
  # ==========================

  - name: ContactId
    type: Uuid

  - name: ContactTimeCreated
    type: RFC3339

  - name: Person
    type: String
    namespace: Funeralzone\FAS\PersonValueObjects\Person
    instantiationName: NullablePerson
    referenceName: Person

  - name: Parents
    type: Set
    children:
      - name: DirectoryListingId

  - name: Status
    type: Enum
    values:
      - LISTED
      - UNLISTED

  - name: Tags
    type: Set
    children:

      - name: Tag
        type: String

deltas:

  - name: PersonDelta
    location: Funeralzone\FAS\PersonValueObjects\Deltas\PersonDelta

  - name: EditContactDelta
    payload:
      - name: Tags
        propertyName: tags

events:

  - name: ContactWasCreated
    payload:
      - name: Person
        propertyName: person
        required: true

      - name: Tags
        propertyName: tags
    meta:
      - name: ContactId
        propertyName: id
        required: true
        key: _aggregate_id

  - name: ContactWasEdited
    deltas:
      - name: PersonDelta
        propertyName: person

      - name: EditContactDelta
        propertyName: delta
        useRootData: false
    meta:
      - name: ContactId
        propertyName: id
        key: _aggregate_id
        required: true

  - name: ContactWasAttachedToDirectoryListing
    payload:
      - name: DirectoryListingId
        propertyName: directoryListing
        required: true
    meta:
      - name: ContactId
        propertyName: id
        key: _aggregate_id
        required: true

  - name: ContactWasUnlisted
    meta:
      - name: ContactId
        propertyName: id
        key: _aggregate_id
        required: true

  - name: NoteWasAdded
    payload:
      - name: Content
        propertyName: content

      - name: AuthorId
        propertyName: author
    meta:
      - name: ContactId
        propertyName: contactId
        key: _aggregate_id
        required: true

  - name: NoteWasEdited
    payload:
      - name: Content
        propertyName: content

      - name: EntityId
        propertyName: id

    meta:
      - name: ContactId
        propertyName: contactId
        key: _aggregate_id
        required: true

  - name: NoteWasRemoved
    payload:
      - name: EntityId
        propertyName: id

    meta:
      - name: ContactId
        propertyName: contactId
        key: _aggregate_id
        required: true

commands:

  - name: CreateContact
    payload:
      - name: ContactId
        propertyName: id
        required: true

      - name: Person
        propertyName: person
        required: true

      - name: Tags
        propertyName: tags

  - name: EditContact
    payload:
      - name: ContactId
        propertyName: id
        required: true
    deltas:
      - name: PersonDelta
        propertyName: person

      - name: EditContactDelta
        propertyName: delta
        useRootData: true

  - name: AttachContactToDirectoryListing
    payload:
      - name: ContactId
        propertyName: id
        required: true

      - name: DirectoryListingId
        propertyName: directoryListing
        required: true

  - name: UnlistContact
    payload:
      - name: ContactId
        propertyName: id

      - name: DirectoryListingId
        propertyName: directoryListing

  - name: AddNote
    payload:
      - name: ContactId
        propertyName: contactId
        required: true

      - name: Content
        propertyName: content

      - name: AuthorId
        propertyName: author

  - name: EditNote
    payload:
      - name: ContactId
        propertyName: contactId
        required: true

      - name: Content
        propertyName: content

      - name: EntityId
        propertyName: id

  - name: RemoveNote
    payload:
      - name: ContactId
        propertyName: contactId
        required: true

      - name: EntityId
        propertyName: id

queries:

  - name: GetContactById
    payload:
      - name: ContactId
        propertyName: id
        required: true

  - name: Search
    payload:
      - name: SearchTerm
        propertyName: term

      - name: SearchLimit
        propertyName: limit