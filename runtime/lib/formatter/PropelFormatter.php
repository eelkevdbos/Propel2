<?php 

/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://propel.phpdb.org>.
 */

/**
 * Abstract class for query formatter
 *
 * @author     Francois Zaninotto
 * @version    $Revision$
 * @package    propel.runtime.formatter
 */
abstract class PropelFormatter
{
	protected
	  $criteria,
	  $class,
	  $peer,
		$currentObjects = array();
	
	public function setCriteria(ModelCriteria $criteria)
	{
		$this->criteria = $criteria;
	}
	
	public function getCriteria()
	{
		return $this->criteria;
	}
	
	abstract public function format(PDOStatement $stmt);

	abstract public function formatOne(PDOStatement $stmt);
	
	abstract public function isObjectFormatter();
	
	/**
	 * Check that a ModelCriteria was properly set
	 *
	 * @throws    PropelException if no Criteria was set, or if the Criteria set is not an instance of ModelCriteria
	 */
	protected function checkCriteria()
	{
		if (null === $this->criteria || !$this->criteria instanceof ModelCriteria) {
			throw new PropelException('A formatter needs a ModelCriteria. Use PropelFormatter::setCriteria() to set one');
		}
		$this->class = $this->getCriteria()->getModelName();
		$this->peer = $this->getCriteria()->getModelPeerName();		
	}
	
	/**
	 * Gets the worker object for the class.
	 * To save memory, we don't create a new object for each row,
	 * But we keep hydrating a single object per class.
	 * The column offset in the row is used to index the array of classes
	 * As there may be more than one object of the same class in the chain
	 * 
	 * @param     int    $col    Offset of the object in the list of objects to hydrate
	 * @param     string $class  Propel model object class
	 * 
	 * @return    BaseObject
	 */
	protected function getWorkerObject($col, $class)
	{
		if(!isset($this->currentObjects[$col])) {
			$this->currentObjects[$col] = new $class();
		}
		return $this->currentObjects[$col];
	}
	
	/**
	 * Gets a Propel object hydrated from a selection of columns in statement row
	 *
	 * @param     array  $row associative array indexed by column number,
	 *                   as returned by PDOStatement::fetch(PDO::FETCH_NUM)
	 * @param     string $class The classname of the object to create
	 * @param     int    $col The start column for the hydration (modified)
	 *
	 * @return    BaseObject
	 */
	public function getSingleObjectFromRow($row, $class, &$col = 0)
	{
		$obj = $this->getWorkerObject($col, $class);
		$col = $obj->hydrate($row, $col);
		
		return $obj;
	}
	
	
}
