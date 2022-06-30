`/`[Home](/service-layer)`/`[1.0](/service-layer/docs/1.0)`/`[Motivation](02-motivation.html)

# Motivation
## Basic description
As stated, Service layer is the library, that aims to approach REST API endpoints 
integration in Service-oriented architecture fashion. Integration with REST API is often pretty straightforward and
easy to implement in one specific case, but often ends up polluting the business
important code by a few means:
* Having technical details on the same level as a business code.
* Having network configuration as part of a code instead of configuration.
* Not being able to reuse same API endpoint integration in a different part of an application.

Service-oriented architecture is an architectural style aiming to provide set of
services to the other components through a communication protocol over network.
