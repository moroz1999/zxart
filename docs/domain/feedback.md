# Feedback form

A fixed contact form with three fields — name, email and message — for visitors
who want to reach the people running the site.

A submission is emailed to the address configured for the form and is not kept
anywhere on the site. The reply goes to the sender's own address, so answering
is a plain reply.

The sender's address is checked before anything is sent: addresses that look
made up, and domains known to be disposable, are refused with an explanation.
The same check guards registration, so an address that works for one works for
the other.

The form's heading, the introduction above it and the recipient are editable.

How it is built: [../features/feedback.md](../features/feedback.md)
