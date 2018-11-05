# Value object generator

## Upgrade notes for V0 to V1

### Everything is a model

There is no longer a strong bias toward Event Sourcing so the concept of _events_, _commands_ and _deltas_ has been removed and the onus of implementing these placed on the types/template library.

The practical upshots of this change are as follows:

#### _events_, _commands_ and _deltas_

- Top level items for _events_, _commands_ and _deltas_ have been removed; every model lives under the _model_ top-level item
- When creating _events_, _commands_ and _deltas_ there is no _deltas_ property; use must define _children_ models that happen to implement delta behaviour
- _events_, _commands_ and _deltas_ are now dedicated **types** - _Event_, _Command_ and _Delta_ respectively

#### Seeding models from Prooph meta data

The _Event_, _Command_ and _Delta_ **types** include a _fromMetaDataKey_ property that allows you to define a Prooph metadata key to seed from

```yaml
- name: ExampleEvent
  type: Event
  children:
    - name: ExampleEventAggregateId
      type: Uuid
      propertyName: id
      fromMetaDataKey: _aggregateId
``` 

#### Deltas seeded with the root payload

Deltas can no longer automatically receive the root payload of a _command_/_event_. 

In V0 you could optionally supply a _useRootData_ flag in the definition which meant the _delta_ received the entire payload of the _event_ or _command_ rather than just its specific property. This is no longer possible.

In order to re-create this behaviour you can bundle up the necessary properties within the Prooph resolver and add a fabricated array property to the _command_/_events_ payload. 

#### Sets no longer accept child models

In V0 sets would require a single child model which inferred the type of contents the _Set_ would accept. This is no longer possible. 

In V1 you must explicitly define the type of model a _Set_ can contain by setting the _modelToEnforce_ property. This property must reference a valid model. 

```yaml
- name: OrganisationalUnitId
  type: Uuid

- name: OrganisationalUnitIds
  type: Set
  modelToEnforce: OrganisationalUnitId
```

## Example definition

```yaml
namespace: ValueObjects

model:

  # External models
  # ==========================

  - name: EntityId
    type: String
    namespace: Funeralzone\FAS\DomainEntities
    testing:
      fromNative: "'1'"
      constructor: "'1'"

  # Data models
  # ==========================
  - name: TenantId
    type: Uuid
    
  - name: BereavedId
    type: Uuid

  - name: ContactId
    type: Uuid

  - name: DirectoryListingId
    type: Uuid

  - name: MediaId
    type: String
    decorators:
    - path: Funeralzone\FAS\Common\ValueObjects\Decorators\MediaIdDecoratorTrait
      hooks:
      - type: constructor
        method: decoratorConstruct

  - name: MediaUploadRequestId
    type: String

  - name: ProductId
    type: Uuid

  - name: ServiceId
    type: Uuid

  - name: StaffMemberId
    type: Uuid

  - name: PackageId
    type: Uuid

  # Telephones
  # --------------------------

  - name: TelephoneNumber
    type: String

  - name: TelephoneCountryCode
    type: String

  - name: Telephone
    type: Telephone

  - name: TelephoneType
    type: Enum
    values:
      - WORK
      - HOME
      - MOBILE
      - FAX

  # Address
  # --------------------------

  - name: AddressLine1
    type: String

  - name: AddressLine2
    type: String

  - name: Town
    type: String

  - name: County
    type: String

  - name: PostCode
    type: String

  - name: CountryCode
    type: ISOAlpha2CountryCode

  - name: GeoLocation
    type: Composite
    children:
      - name: AddressData
        type: String
        propertyName: data
      - name: Geometry
        type: Composite
        propertyName: geometry
        children:
          - name: Latitude
            type: Float
            propertyName: lat
            decorators:
            - path: Funeralzone\FAS\Common\ValueObjects\Decorators\LatitudeDecoratorTrait
              hooks:
              - type: constructor
                method: decoratorConstruct
            testing:
              fromNative: '50.9'
              constructor: '50.9'
          - name: Longitude
            type: Float
            propertyName: lng
            testing:
              fromNative: '50.9'
              constructor: '50.9'
            decorators:
            - path: Funeralzone\FAS\Common\ValueObjects\Decorators\LongitudeDecoratorTrait
              hooks:
              - type: constructor
                method: decoratorConstruct

  # Identity interface
  # --------------------------

  - name: IdentityInterfacePolicyMembership
    type: String

  - name: IdentityInterfacePolicyScope
    type: String

  - name: IdentityInterface
    type: Composite
    children:
      - name: IdentityInterfaceType
        type: Enum
        propertyName: type
        values:
          - STAFF_MEMBER
          - DEVELOPER
      - name: IdentityInterfaceName
        type: String
        propertyName: name
      - name: IdentityInterfaceImage
        type: String
        propertyName: image
      - name: IdentityInterfaceEmail
        type: Email
        propertyName: email
      - name: IdentityInterfacePolicy
        type: Composite
        propertyName: policy
        children:
          - name: IdentityInterfacePolicyMemberships
            type: Set
            propertyName: memberships
            modelToEnforce: IdentityInterfacePolicyMembership

          - name: IdentityInterfacePolicyScopes
            type: Set
            propertyName: scopes
            modelToEnforce: IdentityInterfacePolicyScope

  # Other
  # --------------------------

#  - name: Note
#    type: Entity
#    children:
#    - name: EntityId
#      propertyName: id
#    - name: NoteTimeCreated
#      type: RFC3339
#      propertyName: timeCreated
#    - name: NoteContent
#      type: String
#      propertyName: content
#    - name: NoteAuthorId
#      type: Uuid
#      propertyName: authorId
#
#  - name: Notes
#    type: EntitySet
#    modelToEnforce: Note

  - name: Name
    type: Composite
    children:
      - name: NameTitle
        type: String
        propertyName: title
      - name: GivenName
        type: String
        propertyName: givenName
      - name: FamilyName
        type: String
        propertyName: familyName

  - name: PersonPhone
    type: TelephoneContact
    typeValues:
    - WORK
    - HOME
    - MOBILE
    - FAX

  - name: PersonEmail
    type: Email

  - name: Person
    type: Composite
    children:
      - name: Name
        propertyName: name

      - name: PersonAddress
        type: Address
        propertyName: address

      - name: PersonPhones
        type: Set
        propertyName: phones
        modelToEnforce: PersonPhone

      - name: PersonEmails
        type: Set
        propertyName: emails
        modelToEnforce: PersonEmail

      - name: MediaId
        propertyName: image

  - name: DirectoryListingIds
    type: Set
    modelToEnforce: DirectoryListingId

  - name: OrganisationalUnitId
    type: Uuid

  - name: OrganisationalUnitIds
    type: Set
    modelToEnforce: OrganisationalUnitId

  - name: EstimateId
    type: Uuid

  - name: EstimateIds
    type: Set
    modelToEnforce: EstimateId

  - name: Tag
    type: String
    decorators:
    - path: Funeralzone\FAS\Common\ValueObjects\Decorators\TagDecoratorTrait
      hooks:
      - type: constructor
        method: decoratorConstruct

  - name: Tags
    type: Set
    decorators:
    - path: Funeralzone\FAS\Common\ValueObjects\Decorators\TagsDecoratorTrait
    modelToEnforce: Tag

  - name: PaginationInput
    type: Composite
    children:
      - name: PaginationInputPage
        type: Integer
        propertyName: page
      - name: PaginationInputNodesPerPage
        type: Integer
        propertyName: nodesPerPage

  - name: StaffRoleId
    type: Uuid

  - name: StaffRoleIds
    type: Set
    modelToEnforce: StaffRoleId

  - name: DeveloperId
    type: Uuid

  - name: DeveloperIds
    type: Set
    modelToEnforce: DeveloperId

  - name: PersonDelta
    type: Delta
    children:
      - name: Name
        propertyName: name
      - name: PersonAddress
        propertyName: address
      - name: PersonPhones
        propertyName: phones
      - name: PersonEmails
        propertyName: emails
      - name: MediaId
        propertyName: image
```