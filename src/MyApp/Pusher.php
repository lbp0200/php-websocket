<?php
namespace MyApp;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage;
	}
	public function onUnSubscribe(ConnectionInterface $conn, $topic) {
	}
	public function onOpen(ConnectionInterface $conn) {
		// Store the new connection to send messages to later
		$this->clients->attach($conn);
		echo "New connection! ({$conn->resourceId})\n";
	}
	public function onClose(ConnectionInterface $conn) {
	}
	public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
		// In this application if clients send data it's because the user hacked around in console
		$conn->callError($id, $topic, 'You are not allowed to make calls')->close();
	}
	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
		// In this application if clients send data it's because the user hacked around in console
		$conn->close();
	}
	public function onError(ConnectionInterface $conn, \Exception $e) {
	}/**
	 * A lookup of all the topics clients have subscribed to
	 */
	protected $subscribedTopics = array();

	public function onSubscribe(ConnectionInterface $conn, $topic) {
		$this->subscribedTopics[$topic->getId()] = $topic;
	}

	/**
	 * @param string JSON'ified string we'll receive from ZeroMQ
	 */
	public function onBlogEntry($entry) {
		echo "$entry\n";
		//$topic = $this->subscribedTopics[$entry];

		// re-send the data to all the clients subscribed to that category
		// $topic->broadcast($entry);
		foreach ($this->clients as $client) {
			//if ($from !== $client) {
			// The sender is not the receiver, send to each client connected
			$client->send($entry);
			// }
		}
	}

	/* The rest of our methods were as they were, omitted from docs to save space */
}