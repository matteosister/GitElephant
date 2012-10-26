todo
----

0.8.*

* add interface for caller DONE
* add interface for binary
* commits count

0.9.*
* isolate objects like grit, clean constructor of Commit, Log, Tag, Tree, Diff by accepting the repository as mandatory argument, and a sha as optional argument
* find a way to populate object props from the sha inside the objects
* inject the caller and the command to the objects to populate props
* use sha (default to HEAD) whenever it's possible inside constructors

1.0.0
* git blame
* blobs management
