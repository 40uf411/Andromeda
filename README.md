# Andromeda
Andromeda is a database connector to many famous DBMS, it also include a no-sql DBMS.

-------------------------------------------------------------------------------------

# Auhtentification
when Andromeda is initialized it sets a global variable that containes a User object, by default that user is called "Guest" and it can do almost nothing.
in order to log in, you use ```Andromeda::connect("root","root");``` this function update the global variable to the new user and return the new user.
the user ```root``` is by default the super user, its name can not be changed and it can not lose the Administration privilege.
## Users managment
in order to create edit or remove a user you must be an admin or have the privilege to do that action.
### Create a user
the privilage required to create a new user is ```insert on System.users```
to create new user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
```
the function will create and return the User object
### Edite a user
the privilage required to create a user is ```update on System.users```
to edite a user:
```
$user = Andromeda::connect("root","root");
$u = $user->edite_user("Guest");
```
the function will create and return the User object
### Remove a user
the privilage required to create a user is ```delete on System.users```
to delete a user:
```
$user = Andromeda::connect("root","root");
$u = $user->delete_user("Guest");
```
### Lock a user
when you create a new user it is by default locked, but you can also lock an active user and it will no longer be able to log into the system untill its unlocked, the ```root``` user can not be locked.
the privilage required to lock a new user is ```update on System.users```
to edite a user:
```
$user = Andromeda::connect("root","root");
$u = $user->lock();
```
### Unlock a user
the privilage required to lock a new user is ```update on System.users```
to edite a user:
```
$user = Andromeda::connect("root","root");
$u = $user->unlock();
```
### Add or edit an attributes
the privilage required to create a new user is ```update on System.users```
to add an attribute to a user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
$u->set_attribute("country", "DZ");
```
### remove an attributes
the privilage required to create a new user is ```update on System.users```
to add an attribute to a user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
$u->remove_attribute("country");
```
### Check for attributes
the privilage required to create a new user is ```update on System.users```
to add an attribute to a user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
$u->has_attribute("country");
```
### get an attributes
the privilage required to create a new user is ```update on System.users```
to add an attribute to a user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
$u->get_attribute("select", "value", "users");
```
### Add a privilage
the privilage required to create a new user is ```update on System.users```
to add an attribute to a user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
$u->set_privilege("select", "value", "users");
```
### remove a privilage
the privilage required to create a new user is ```update on System.users```
to add an attribute to a user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
$u->remove_privilege("select", "value", "users");
```
### Check for privilage
the privilage required to create a new user is ```update on System.users```
to add an attribute to a user:
```
$user = Andromeda::connect("root","root");
$u = $user->create_user("Guest");
$u->can("select", "value", "users");
```
### Save changes
to save changes:
```
$u->save();
```
## List of privilage
### On database
* create
* importe
* drop
### On database's tables
* select
* insert
* update
* delete
-------------------------------------------------------------------------------------

